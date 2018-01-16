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
use Elastica\Query\Match;
use Elastica\Query\Terms;

class SearchController extends BaseController
{
    protected function processSearchRequest(Request $request)
    {
        $searchTerm = '*' . $request->get('q') . '*';
        $page = $request->get('page', 1);
        $perPage = $this->container->getParameter('blast_search')['results_per_page'];
        $defaultIndex = $this->container->getParameter('blast_search')['global_index_alias'];
        $index = $request->get('index', $defaultIndex);
        $type = $request->get('type', null);

        if ($index === '') {
            $index = 'global';
        }

        $finderName = 'fos_elastica.finder.' . $index;

        if ($type !== null && $type !== '') {
            $finderName .= '.' . $type;
        }

        $finder = $this->container->get($finderName);

        if ($filter = $request->get('filter', null)) {
            $query = $this->processFilteredQuery($finder, $filter, $searchTerm);
        } else {
            $query = $searchTerm;
        }

        $paginator = $this->container->get('knp_paginator');
        $results = $finder->createPaginatorAdapter($query);
        $pagination = $paginator->paginate($results, $page, $perPage);

        return $pagination;
    }

    protected function processFilteredQuery($finder, $filter, $searchTerm)
    {
        $filter = explode('|', $filter);

        $boolQuery = new BoolQuery();

        $termQuery = new Terms();
        $termQuery->setTerms('*', [$searchTerm]);
        $boolQuery->addShould($termQuery);

        $filterQuery = new Match();
        $filterQuery->setFieldQuery($filter[0], $filter[1]);
        $boolQuery->addMust($filterQuery);

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
