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

namespace tests\units\CCMBenchmark\TingGenerator\Database\Analyzer;

use CCMBenchmark\TingGenerator\Database\RepositoryFactory;
use CCMBenchmark\TingGenerator\Database\Analyzer\MySQL\TableAnalyzer;
use CCMBenchmark\TingGenerator\Database\Analyzer\MySQL\TypeMapping;
use mageekguy\atoum;
use Psr\Log\LoggerInterface;
use CCMBenchmark\TingGenerator\Database\Repository;

/**
 * Class AnalyzerFactory
 * @package tests\units\CCMBenchmark\TingGenerator\Database\Analyzer
 * @tags AnalyzerFactory
 */
class AnalyzerFactory extends atoum
{
    /**
     * @var RepositoryFactory
     */
    private $repositoryFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @param string $method
     */
    public function beforeTestMethod($method)
    {
        $this->mockGenerator->orphanize('__construct');
        $this->repositoryFactory = new \mock\CCMBenchmark\TingGenerator\Database\RepositoryFactory();

        $this->logger = new \mock\Psr\Log\LoggerInterface();

        $this->mockGenerator->orphanize('__construct');
        $this->repository = new \mock\CCMBenchmark\TingGenerator\Database\Repository();
    }

    public function testGetFromTypeReturnMySQLAnalyzer()
    {
        $this
            ->given($this->calling($this->repositoryFactory)->getRepository = $this->repository)
            ->and($analyzerExpected = new TableAnalyzer(new TypeMapping(), $this->logger, $this->repository))
            ->and($this->newTestedInstance($this->repositoryFactory, $this->logger))
                ->object($this->testedInstance->getFromType('mysql'))
                    ->isEqualTo($analyzerExpected);
    }

    public function testGetFromTypeReturnNullOnUnknownType()
    {
        $this
            ->given($this->newTestedInstance($this->repositoryFactory, $this->logger))
                ->variable($this->testedInstance->getFromType(uniqid('type')))
                    ->isNull();
    }
}
