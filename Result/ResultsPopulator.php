<?php

/*
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\SearchBundle\Result;

use Elastica\Result;
use FOS\ElasticaBundle\Configuration\ConfigManager;
use FOS\ElasticaBundle\Doctrine\ORM\ElasticaToModelTransformer;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ResultsPopulator
{
    /**
     * @var ConfigManager
     */
    private $fosElasticaConfigManager;

    /**
     * @var RegistryInterface
     */
    private $doctrine;

    public function populateSearchResults($results)
    {
        $resultSet = [];
        $entities = [];

        foreach ($results['hits']['hits'] as $hit) {
            $elasticResult = new Result($hit);
            $type = $elasticResult->getType();
            $index = $elasticResult->getIndex();
            $id = $elasticResult->getId();

            $model = $this->fosElasticaConfigManager->getTypeConfiguration($index, $type)->getModel();
            if (!array_key_exists($model, $resultSet)) {
                $resultSet[$model] = [];
            }

            $resultSet[$model][] = $elasticResult;
        }

        foreach ($resultSet as $model => $result) {
            $transformer = new ElasticaToModelTransformer($this->doctrine, $model, [
                'ignore_missing'       => true,
                'hydrate'              => true,
                'query_builder_method' => 'createQueryBuilder',
            ]);
            $transformer->setPropertyAccessor(new PropertyAccessor());
            $entities = array_merge($entities, $transformer->transform($result, true));
        }

        return $entities;
    }

    /**
     * @param ConfigManager $fosElasticaConfigManager
     */
    public function setFosElasticaConfigManager(ConfigManager $fosElasticaConfigManager): void
    {
        $this->fosElasticaConfigManager = $fosElasticaConfigManager;
    }

    /**
     * @param RegistryInterface $doctrine
     */
    public function setDoctrine(RegistryInterface $doctrine): void
    {
        $this->doctrine = $doctrine;
    }
}
