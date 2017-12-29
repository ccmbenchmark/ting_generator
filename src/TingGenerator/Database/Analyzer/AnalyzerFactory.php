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

namespace CCMBenchmark\TingGenerator\Database\Analyzer;

use CCMBenchmark\TingGenerator\Database\Analyzer\MySQL\TableAnalyzer;
use CCMBenchmark\TingGenerator\Database\Analyzer\MySQL\TypeMapping;
use CCMBenchmark\TingGenerator\Database\RepositoryFactory;
use Psr\Log\LoggerInterface;

class AnalyzerFactory
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
     * AnalyzerFactory constructor.
     * @param RepositoryFactory $repositoryFactory
     * @param LoggerInterface $logger
     */
    public function __construct(RepositoryFactory $repositoryFactory, LoggerInterface $logger)
    {
        $this->repositoryFactory = $repositoryFactory;
        $this->logger = $logger;
    }

    /**
     * @param string $type
     *
     * @return TableAnalyzerInterface|null
     */
    public function getFromType($type)
    {
        if ($type === 'mysql') {
            return new TableAnalyzer(
                new TypeMapping(),
                $this->logger,
                $this->repositoryFactory->getRepository()
            );
        }

        $this->logger->error('Unknown table analyzer type "' . $type . '"');
        return null;
    }
}
