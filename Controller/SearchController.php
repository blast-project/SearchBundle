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

class SearchController extends BaseController
{
    protected function processSearchRequest(Request $request)
    {
        $searchTerm = $request->get('q') . '*';
        $page = $request->get('page', 1);
        $perPage = $this->container->getParameter('blast_search')['results_per_page'];
        $index = $request->get('index', 'global');
        $type = $request->get('type', null);

        if ($index === '') {
            $index = 'global';
        }

        $finderName = 'fos_elastica.finder.' . $index;

        if ($type !== null && $type !== '') {
            $finderName .= '.' . $type;
        }

        $finder = $this->container->get($finderName);

        $paginator = $this->container->get('knp_paginator');
        $results = $finder->createPaginatorAdapter($searchTerm);
        $pagination = $paginator->paginate($results, $page, $perPage);

        return $pagination;
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
