<?php
/***********************************************************************
 *
 * Ting Generator
 * ==========================================
 *
 * Copyright (C) 2017 CCM Benchmark Group. (http://www.ccmbenchmark.com)
 *
 ***********************************************************************
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you
 * may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
 * implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 **********************************************************************/

namespace tests\units\CCMBenchmark\TingGenerator\Database;

use mageekguy\atoum;
use CCMBenchmark\TingGenerator\Database;
use CCMBenchmark\Ting\ConnectionPoolInterface;
use CCMBenchmark\Ting\MetadataRepository;
use CCMBenchmark\Ting\Repository\RepositoryFactory as TingRepositoryFactory;

/**
 * Class RepositoryFactory
 * @package tests\units\CCMBenchmark\TingGenerator\Database
 * @tags RepositoryFactory
 */
class RepositoryFactory extends atoum
{
    /**
     * @var ConnectionPoolInterface
     */
    private $connectionPool;

    /**
     * @var MetadataRepository
     */
    private $metadataRepository;

    /**
     * @var TingRepositoryFactory
     */
    private $tingRepositoryFactory;

    /**
     * @var Database\Repository
     */
    private $repository;

    /**
     * @param string $method
     */
    public function beforeTestMethod($method)
    {
        $this->mockGenerator->orphanize('__construct');
        $this->connectionPool = new \mock\CCMBenchmark\Ting\ConnectionPoolInterface();
        $this->calling($this->connectionPool)->setConfig = null;

        $this->mockGenerator->orphanize('__construct');
        $this->metadataRepository = new \mock\CCMBenchmark\Ting\MetadataRepository();
        $this->calling($this->metadataRepository)->batchLoadMetadata = [];

        $this->mockGenerator->orphanize('__construct');
        $this->repository = new \mock\CCMBenchmark\TingGenerator\Database\Repository();

        $this->mockGenerator->orphanize('__construct');
        $this->tingRepositoryFactory = new \mock\CCMBenchmark\Ting\Repository\RepositoryFactory();
        $this->calling($this->tingRepositoryFactory)->get = $this->repository;
    }

    public function testGetRepositoryReturnRepository()
    {
        $this
            ->given(
                $this->newTestedInstance(
                    new Database\ConnectionData(
                        uniqid('type'),
                        uniqid('host'),
                        uniqid('userName'),
                        uniqid('password'),
                        rand(0, PHP_INT_MAX),
                        uniqid('charset'),
                        uniqid('database')
                    ),
                    $this->connectionPool,
                    $this->metadataRepository,
                    $this->tingRepositoryFactory
                )
            )
            ->object($this->testedInstance->getRepository())
                ->isIdenticalTo($this->repository);
        ;
    }

    public function testFactoryHandleConnectionTypePgSQL()
    {
        $this
            ->given(
                $connectionData = new Database\ConnectionData(
                    'pgsql',
                    uniqid('host'),
                    uniqid('userName'),
                    uniqid('password'),
                    rand(0, PHP_INT_MAX),
                    uniqid('charset'),
                    uniqid('database')
                )
            )
            ->and(
                $this->newTestedInstance(
                    $connectionData,
                    $this->connectionPool,
                    $this->metadataRepository,
                    $this->tingRepositoryFactory
                )
            )
            ->mock($this->connectionPool)
                ->call('setConfig')
                    ->withIdenticalArguments(
                        [
                            'main' => [
                                'namespace' => '\CCMBenchmark\Ting\Driver\Pgsql',
                                'master' => [
                                    'host' => $connectionData->getHost(),
                                    'user' => $connectionData->getUserName(),
                                    'password' => $connectionData->getPassword(),
                                    'port' => $connectionData->getPort(),
                                    'charset' => $connectionData->getCharset()
                                ]
                            ]
                        ]
                    )
                        ->once()
        ;
    }

    public function testFactoryHandleConnectionTypeMySQL()
    {
        $this
            ->given(
                $connectionData = new Database\ConnectionData(
                    'mysqli',
                    uniqid('host'),
                    uniqid('userName'),
                    uniqid('password'),
                    rand(0, PHP_INT_MAX),
                    uniqid('charset'),
                    uniqid('database')
                )
            )
            ->and(
                $this->newTestedInstance(
                    $connectionData,
                    $this->connectionPool,
                    $this->metadataRepository,
                    $this->tingRepositoryFactory
                )
            )
            ->mock($this->connectionPool)
            ->call('setConfig')
            ->withIdenticalArguments(
                [
                    'main' => [
                        'namespace' => '\CCMBenchmark\Ting\Driver\Mysqli',
                        'master' => [
                            'host' => $connectionData->getHost(),
                            'user' => $connectionData->getUserName(),
                            'password' => $connectionData->getPassword(),
                            'port' => $connectionData->getPort(),
                            'charset' => $connectionData->getCharset()
                        ]
                    ]
                ]
            )
            ->once()
        ;
    }

    public function testFactoryLoadMetadata()
    {
        $this
            ->given(
                $connectionData = new Database\ConnectionData(
                    uniqid('type'),
                    uniqid('host'),
                    uniqid('userName'),
                    uniqid('password'),
                    rand(0, PHP_INT_MAX),
                    uniqid('charset'),
                    uniqid('database')
                )
            )
            ->and(
                /**
                 * Get the same __DIR__ value as if we were RepositoryFactory.
                 * /var/www/ting_generator/tests/units/TingGenerator/Database
                 * became
                 * /var/www/ting_generator/src/TingGenerator/Database
                 */
                $dir = preg_replace_callback(
                    '~(?P<basePath>.*\/ting_generator).*~',
                    function ($match) {
                        return $match['basePath'] . '/src/TingGenerator/Database';
                    },
                    __DIR__
                )
            )
            ->and(
                $this->newTestedInstance(
                    $connectionData,
                    $this->connectionPool,
                    $this->metadataRepository,
                    $this->tingRepositoryFactory
                )
            )
            ->mock($this->metadataRepository)
                ->call('batchLoadMetadata')
                    ->withIdenticalArguments(
                        'CCMBenchmark\TingGenerator\Database',
                        $dir . '/Repository.php',
                        [
                            'CCMBenchmark\TingGenerator\Database\Repository' => [
                                'connection' => 'main',
                                'database' => $connectionData->getDatabase()
                            ]
                        ]
                    )
                        ->once()
        ;
    }
}
