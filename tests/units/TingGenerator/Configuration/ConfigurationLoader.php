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

namespace tests\units\CCMBenchmark\TingGenerator\Configuration;

use mageekguy\atoum;
use Psr\Log\LoggerInterface;

/**
 * Class ConfigurationLoader
 * @package tests\units\CCMBenchmark\TingGenerator\Configuration
 * @tags ConfigurationLoader
 */
class ConfigurationLoader extends atoum
{
    /**
     * @var LoggerInterface;
     */
    private $logger;

    /**
     * @param string $method
     */
    public function beforeTestMethod($method)
    {
        $this->logger = new \mock\Psr\Log\LoggerInterface();
    }

    /**
     * Test 'loadFromFile' return null if configuration file doesn't exist.
     */
    public function testLoadFromFileReturnNullIfConfigurationFileNotExist()
    {
        $this
            ->given($filename = uniqid('filename'))
            ->and($this->function->file_exists = false)
            ->and($this->newTestedInstance($this->logger))
            ->variable($this->testedInstance->loadFromFile($filename))
                ->isNull();
    }
}
