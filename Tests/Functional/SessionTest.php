<?php

/*
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace  Blast\Bundle\DoctrineSessionBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Blast\Bundle\DoctrineSessionBundle\Handler\DoctrineORMHandler;
use Symfony\Component\HttpFoundation\Session\Session;

/*
 * @todo: check if Entity\Session why (it need to) implement SessionInterface
 use Blast\Bundle\DoctrineSessionBundle\Entity\Session;
*/

/*
 * for test on session implementation only
 use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeSessionHandler;
 use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
 use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;
 use Doctrine\ORM\Query\ResultSetMapping;
*/

/**
 * Needed as php allow only one session by process.
 *
 * @runTestsInSeparateProcesses
 */
class SessionTest extends KernelTestCase
{
    protected $entitymanager;
    protected $registrymanager;
    protected $repository;
    protected $sessionclass;
    protected $doctrinehandler;
    protected $session;
    protected $sessionid;
    protected $lifetime;

    protected function setUp()
    {
        static::bootKernel();
        /*
         * SELECT table_name FROM information_schema.tables where TABLE_SCHEMA='travis'
         *  select * from sf_session
         *  @todo: get result from database to check if session exist
         */

        $this->registrymanager = static::$kernel
                               ->getContainer()
                               ->get('doctrine');

        $this->sessionclass = 'Blast\Bundle\DoctrineSessionBundle\Entity\Session';
        /* @warning: session is handled by a specific orm cnx */
        $this->entitymanager = $this->registrymanager->getManagerForClass($this->sessionclass);
        $this->repository = $this->entitymanager->getRepository($this->sessionclass);
        // $this->registrymanager
        //                  ->getManager('session');

        $this->doctrinehandler = new DoctrineORMHandler($this->registrymanager, $this->sessionclass);

        /*
         * Need to disable cookies to avoid error
         * RuntimeException: Failed to start the session because headers have already been sent...
         * from NativeSessionStorage line 134
         * bug without the phpunit  --process-isolation
         */
        //$this->storage = new NativeSessionStorage(['use_cookies' => false], $this->doctrinehandler);
        //$this->storage = new NativeSessionStorage(['use_cookies' => false], new NativeSessionHandler());
        //$this->storage = new PhpBridgeSessionStorage();
        //$this->storage = new MockArraySessionStorage();

        $this->lifetime = 1;
        $this->storage = new NativeSessionStorage(
            array(),
            $this->doctrinehandler
        );
        $this->storage->setOptions(array(
            'cookie_lifetime' => $this->lifetime, // for metabag
            'gc_maxlifetime'  => $this->lifetime, // for gc
        ));
        $this->session = new Session($this->storage);
        $this->session->start();
        $this->sessionid = $this->session->getId();
    }

    public function tearDown()
    {
    }

    public function testIsStarted()
    {
        /*
         *@todo check if session->isStarted is well implemented
         */
        $this->assertTrue($this->session->isStarted());
        $this->assertTrue($this->storage->isStarted());
    }

    public function testIsSessionInDB()
    {
        $dbRes = $this->getArrayFromDb($this->sessionid);
        //$this->assertEquals($this->session->getId(), $dbRes[0]['sessionId']);
        $this->assertArrayHasKey('createdAt', $dbRes[0]);
        $this->assertArrayHasKey('expiresAt', $dbRes[0]);
        $this->assertArraySubset(['sessionId' => $this->sessionid], $dbRes[0]);
    }

    public function testInvalidate()
    {
        /*
         * @todo: check why invalidate failed with recent php
         *  due to session_regenerate_id(true);
         *
         */
        $this->markTestSkipped(
            'work only with php 5.6'
        );
        $this->assertEquals($this->sessionid, $this->session->getId());
        $this->session->invalidate();
        $this->assertNotEquals($this->sessionid, $this->session->getId());
    }

    public function testMigrate()
    {
        $this->assertEquals($this->sessionid, $this->session->getId());
        $this->session->migrate();
        $this->assertNotEquals($this->sessionid, $this->session->getId());
    }

    public function testLifetime()
    {
        $this->assertEquals(
            $this->lifetime,
            $this->session->getMetadataBag()->getLifetime()
        );
    }

    public function testClearSession()
    {
        $this->session->set('foo', 'bar');
        $this->assertEquals('bar', $this->session->get('foo'));
        $this->session->clear();
        $this->assertNull($this->session->get('foo'));
    }

    public function testClearStorage()
    {
        $this->session->set('foo', 'bar');
        $this->assertEquals('bar', $this->session->get('foo'));
        $this->storage->clear();
        $this->assertNull($this->session->get('foo'));
    }

    public function testGc()
    {
        $this->assertCount(1, $this->getArrayFromDb($this->sessionid));
        /*
         *   @warning: test may take a lot of time
         * used to be sure session has expired
         */
        sleep($this->lifetime + 2);
        // One more
        //        sleep(1);

        /*
         * @todo : check if param is used or not
         */
        $this->doctrinehandler->gc(0);
        $this->assertCount(0, $this->getArrayFromDb($this->sessionid));
    }

    public function testDestroy()
    {
        $this->assertCount(1, $this->getArrayFromDb($this->sessionid));
        $this->doctrinehandler->destroy($this->sessionid);
        $this->assertCount(0, $this->getArrayFromDb($this->sessionid));
    }

    protected function getArrayFromDb($sessionId)
    {
        /* to be able to get data from database */
        $this->session->save();
        //$this->entitymanager->clear();
        //$this->entitymanager->flush();

        $query = $this->repository
               ->createQueryBuilder('s')
               ->select()
               ->where('s.sessionId = :session_id')
               ->setParameter('session_id', $sessionId)
               ->getQuery();

        $arrayRes = $query->getArrayResult();

        return $arrayRes;
    }
}
