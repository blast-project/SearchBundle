<?php

/*
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Tests\Functional;

use Blast\Bundle\TestsBundle\Functional\BlastTestCase;

class CollectGarbageCommandTest extends BlastTestCase
{
    protected function setUp()
    {
        parent::setUp();
        // $this->cacheClear();
        // $this->updateSchema();
        // $this->validateSchema();
    }

    protected function tearDown()
    {
        // $this->dropDatabase();
    }

    public function testCommand()
    {
        /*
         * @todo: need a simple way to add session and check if it is well garbaged
         *
         */
        $this->launchCommand([
            'command' => 'blast:session:collect-garbage',
            '--all'   => true,
        ]);

        /*
         * @todo : should check if there are session in database or not
         */
        $this->launchCommand([
            'command' => 'blast:session:collect-garbage',
            'limit'   => '3',
        ]);
    }
}
