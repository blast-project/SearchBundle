<?php

/*
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\DoctrineSessionBundle\DependencyInjection\Test\Unit;

use Blast\Bundle\DoctrineSessionBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    /**
     * @var Configuration
     */
    protected $object;
    protected $testtree;

    protected function setUp()
    {
        $this->object = new Configuration();
    }

    protected function tearDown()
    {
    }

    /**
     * @covers \Blast\Bundle\DoctrineSessionBundle\DependencyInjection\Configuration::getConfigTreeBuilder
     */
    public function testGetConfigTreeBuilder()
    {
        $this->testtree = $this->object->getConfigTreeBuilder();
        $this->assertInstanceOf(
            'Symfony\Component\Config\Definition\Builder\TreeBuilder',
            $this->testtree
        );
        /*
         * @TODO maybe we need to add a test of root name or some other content
         */
    }
}
