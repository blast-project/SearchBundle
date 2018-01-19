<?php

/*
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\SearchBundle\Search;

use Elastica\Request as ElasticaRequest;
use FOS\ElasticaBundle\Configuration\ConfigManager;
use FOS\ElasticaBundle\Elastica\Client;
use Knp\Component\Pager\Paginator;
use Blast\Bundle\SearchBundle\Result\ResultsPopulator;

class GlobalSearch
{
    /**
     * @var Client
     */
    private $fosElasticaClient;

    /**
     * @var ConfigManager
     */
    private $fosElasticaConfigManager;

    /**
     * @var ResultsPopulator
     */
    private $resultsPopulator;

    /**
     * @var Paginator
     */
    private $paginator;

    public function processGlobalSearch($searchTerm, $page, $perPage)
    {
        $query = [
            'query' => [
                'query_string' => [
                    'query' => $searchTerm . '*',
                ],
            ],
            'from' => ($page - 1) * $perPage,
            'size' => $perPage,
        ];

        $esUrl = implode(',', $this->fosElasticaConfigManager->getIndexNames()) . '/_search';

        $results = $this->fosElasticaClient->request($esUrl, ElasticaRequest::GET, $query);
        $json = $results->getData();

        $results = $this->resultsPopulator->populateSearchResults($json, $this->fosElasticaConfigManager);

        $pagination = $this->paginator->paginate($results, $page, $perPage);

        $pagination->setTotalItemCount($json['hits']['total']);
        $pagination->setItems($results);

        return $pagination;
    }

    /**
     * @param Client $fosElasticaClient
     */
    public function setFosElasticaClient(Client $fosElasticaClient): void
    {
        $this->fosElasticaClient = $fosElasticaClient;
    }

    /**
     * @param ConfigManager $fosElasticaConfigManager
     */
    public function setFosElasticaConfigManager(ConfigManager $fosElasticaConfigManager): void
    {
        $this->fosElasticaConfigManager = $fosElasticaConfigManager;
    }

    /**
     * @param ResultsPopulator $resultsPopulator
     */
    public function setResultsPopulator(ResultsPopulator $resultsPopulator): void
    {
        $this->resultsPopulator = $resultsPopulator;
    }

    /**
     * @param Paginator $paginator
     */
    public function setPaginator(Paginator $paginator): void
    {
        $this->paginator = $paginator;
    }
}
