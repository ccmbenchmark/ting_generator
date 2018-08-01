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

namespace tests\units\CCMBenchmark\TingGenerator\Database\Analyzer\MySQL;

use CCMBenchmark\Ting\Query\Query;
use CCMBenchmark\Ting\Query\QueryException;
use CCMBenchmark\TingGenerator\Database\Analyzer\TypeMapperInterface;
use CCMBenchmark\TingGenerator\Database\FieldDescription;
use CCMBenchmark\TingGenerator\Database\Repository;
use CCMBenchmark\TingGenerator\Database\TableDescription;
use mageekguy\atoum;
use Psr\Log\LoggerInterface;

/**
 * Class TableAnalyzer
 * @package tests\units\CCMBenchmark\TingGenerator\Database\Analyzer\MySQL
 * @tags MySQLTableAnalyzer
 */
class TableAnalyzer extends atoum
{
    /**
     * @var TypeMapperInterface
     */
    private $typeMapping;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var Query
     */
    private $query;

    /**
     * @param string $method
     */
    public function beforeTestMethod($method)
    {
        $this->typeMapping = new \mock\CCMBenchmark\TingGenerator\Database\Analyzer\TypeMapperInterface();
        $this->logger = new \mock\Psr\Log\LoggerInterface();

        $this->mockGenerator->orphanize('__construct');
        $this->repository = new \mock\CCMBenchmark\TingGenerator\Database\Repository();

        $this->mockGenerator->orphanize('__construct');
        $this->query = new \mock\CCMBenchmark\Ting\Query\Query();
    }

    public function testGetTablesDataReturnTablesData()
    {
        $this
            ->given()
            ->and($table1Name = uniqid('table1'))
            ->and($tablesList = [['Tables_in_auth_ccm_net' => $table1Name]])
            ->and($this->calling($this->query)->query[1] = $tablesList)
            ->and($table1 = [
                [
                    'Field' => uniqid('field'),
                    'Type' => uniqid('type'),
                    'Extra' => 'auto_increment',
                    'Key'  => 'PRI'
                ],
                [
                    'Field' => uniqid('field'),
                    'Type' => uniqid('type'),
                    'Extra' => '',
                    'Key'  => ''
                ]
            ])
            ->and($this->calling($this->query)->query[2] = $table1)

            ->and($typeField1 = uniqid('type'))
            ->and($this->calling($this->typeMapping)->getPhpTypeFromFieldType[1] = $typeField1)
            ->and($typeField2 = uniqid('type'))
            ->and($this->calling($this->typeMapping)->getPhpTypeFromFieldType[2] = $typeField2)

            ->and($resultExpected = [
                $table1Name => new TableDescription(
                    $table1Name,
                    [
                        new FieldDescription($typeField1, $table1[0]['Field'], true, true),
                        new FieldDescription($typeField2, $table1[1]['Field'], false, false)
                    ]
                )
            ])

            ->and($this->calling($this->repository)->getQuery = $this->query)
            ->and($this->calling($this->repository)->getCollection = null)

            ->and($this->newTestedInstance($this->typeMapping, $this->logger, $this->repository))
                ->array($this->testedInstance->getTablesData(uniqid('databasename')))
                    ->isEqualTo($resultExpected)
        ;
    }

    public function testGetTablesDataReturnEmptyArrayOnQueryExceptionWhenRetrievingTablesList()
    {
        $this
            ->given($queryException = new QueryException(uniqid('message')))
            ->and($this->calling($this->query)->query = function () use ($queryException) {
                throw $queryException;
            })
            ->and($this->calling($this->repository)->getQuery = $this->query)
            ->and($this->calling($this->repository)->getCollection = null)
            ->and($this->newTestedInstance($this->typeMapping, $this->logger, $this->repository))
                ->array($this->testedInstance->getTablesData(uniqid('databasename')))
                    ->isEmpty()
            ->mock($this->logger)
                ->call('error')
                    ->withIdenticalArguments($queryException->getMessage())
                        ->once();
    }

    public function testGetTablesDataHandleExcludeFilter()
    {
        $this
            ->given()
            ->and($table1Name = uniqid('table1'))
            ->and($tablesList = [['Tables_in_auth_ccm_net' => $table1Name]])
            ->and($this->calling($this->query)->query[1] = $tablesList)
            ->and($table1 = [
                [
                    'Field' => uniqid('field'),
                    'Type' => uniqid('type'),
                    'Extra' => 'auto_increment',
                    'Key'  => 'PRI'
                ],
                [
                    'Field' => uniqid('field'),
                    'Type' => uniqid('type'),
                    'Extra' => '',
                    'Key'  => ''
                ]
            ])
            ->and($this->calling($this->query)->query[2] = $table1)

            ->and($typeField1 = uniqid('type'))
            ->and($this->calling($this->typeMapping)->getPhpTypeFromFieldType[1] = $typeField1)
            ->and($typeField2 = uniqid('type'))
            ->and($this->calling($this->typeMapping)->getPhpTypeFromFieldType[2] = $typeField2)

            ->and($excludedTablesFilter = function ($tableName) use ($table1Name) {
                return $tableName === $table1Name;
            })

            ->and($resultExpected = [])

            ->and($this->calling($this->repository)->getQuery = $this->query)
            ->and($this->calling($this->repository)->getCollection = null)

            ->and($this->newTestedInstance($this->typeMapping, $this->logger, $this->repository))
                ->array($this->testedInstance->getTablesData(uniqid('databasename'), $excludedTablesFilter))
                    ->isEqualTo($resultExpected)
        ;
    }

    public function testGetTablesDataReturnEmptyArrayOnQueryExceptionWhenRetrievingTableData()
    {
        $this
            ->given()
            ->and($table1Name = uniqid('table1'))
            ->and($tablesList = [['Tables_in_auth_ccm_net' => $table1Name]])
            ->and($this->calling($this->query)->query[1] = $tablesList)

            ->and($this->calling($this->repository)->getQuery = $this->query)
            ->and($this->calling($this->repository)->getCollection = null)

            ->and($queryException = new QueryException(uniqid('message')))
            ->and($this->calling($this->query)->query[2] = function () use ($queryException) {
                throw $queryException;
            })
            ->and($this->newTestedInstance($this->typeMapping, $this->logger, $this->repository))
                ->array($this->testedInstance->getTablesData(uniqid('databasename')))
                    ->isEmpty()
            ->mock($this->logger)
                ->call('error')
                    ->withIdenticalArguments($queryException->getMessage())
                        ->once()
        ;
    }
}
