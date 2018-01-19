<?php

/*
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\SearchBundle\Controller;

use Blast\Bundle\CoreBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Elastica\Query\BoolQuery;
use Elastica\Query\QueryString;

class SearchController extends BaseController
{
    public function processSearchRequest(Request $request)
    {
        $searchTerm = $request->get('q');
        $page = $request->get('page', 1);
        $perPage = $this->container->getParameter('blast_search')['results_per_page'];
        $index = $request->get('index', '_all');

        if ($index === '_all') {
            return $this->container->get('blast_search.search.global_search')->processGlobalSearch($searchTerm, $page, $perPage);
        }

        $finder = $this->container->get('fos_elastica.finder.' . $index);

        if ($filter = $request->get('filter', null)) {
            $query = $this->processFilteredQuery($filter, $searchTerm);
        } else {
            $query = new BoolQuery();
            $termQuery = new QueryString();
            $termQuery->setParam('query', $searchTerm);
            $query->addMust($termQuery);
        }

        $paginator = $this->container->get('knp_paginator');
        $results = $finder->createPaginatorAdapter($query);
        $pagination = $paginator->paginate($results, $page, $perPage);

        return $pagination;
    }

    protected function processFilteredQuery($filter, $searchTerm)
    {
        $filter = explode('|', $filter);

        $boolQuery = new BoolQuery();

        $searchQuery = new QueryString();
        $searchQuery->setParam('query', $filter[1]);
        $searchQuery->setParam('fields', array(
            $filter[0],
        ));
        $boolQuery->addMust($searchQuery);

        $termQuery = new QueryString();
        $termQuery->setParam('query', $searchTerm);
        $boolQuery->addMust($termQuery);

        return $boolQuery;
    }

    public function searchAction(Request $request)
    {
        return $this->render('BlastSearchBundle:Search:results.html.twig', array('results' => $this->processSearchRequest($request)));
    }

    public function ajaxSearchAction(Request $request)
    {
        $response = new Response();
        $response->headers->add(['Content-Type' => 'application/json']);

        return $this->render('BlastSearchBundle:Search:results.json.twig', array('results' => $this->processSearchRequest($request)), $response);
    }
}
