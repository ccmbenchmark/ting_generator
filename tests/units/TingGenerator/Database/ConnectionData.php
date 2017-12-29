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

/**
 * Class ConnectionData
 * @package tests\units\CCMBenchmark\TingGenerator\Database\Analyzer
 * @tags ConnectionData
 */
class ConnectionData extends atoum
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $userName;

    /**
     * @var string
     */
    private $password;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $charset;

    /**
     * @var string
     */
    private $database;

    /**
     * @param $method
     */
    public function beforeTestMethod($method)
    {
        $this->type = uniqid('type');
        $this->host = uniqid('host');
        $this->userName = uniqid('userName');
        $this->password = uniqid('password');
        $this->port = rand(0, PHP_INT_MAX);
        $this->charset = uniqid('charset');
        $this->database = uniqid('database');
    }

    /**
     * @return $this
     */
    private function createTestedInstance()
    {
        return $this
            ->given(
                $this->newTestedInstance(
                    $this->type,
                    $this->host,
                    $this->userName,
                    $this->password,
                    $this->port,
                    $this->charset,
                    $this->database
                )
            );
    }

    public function testGetType()
    {
        $this
            ->createTestedInstance()
            ->string($this->testedInstance->getType())
                ->isIdenticalTo($this->type);
    }

    public function testGetHost()
    {
        $this
            ->createTestedInstance()
            ->string($this->testedInstance->getHost())
                ->isIdenticalTo($this->host);
    }

    public function testGetUserName()
    {
        $this
            ->createTestedInstance()
            ->string($this->testedInstance->getUserName())
                ->isIdenticalTo($this->userName);
    }

    public function testGetPassword()
    {
        $this
            ->createTestedInstance()
            ->string($this->testedInstance->getPassword())
                ->isIdenticalTo($this->password);
    }

    public function testGetPort()
    {
        $this
            ->createTestedInstance()
            ->integer($this->testedInstance->getPort())
                ->isIdenticalTo($this->port);
    }

    public function testGetCharset()
    {
        $this
            ->createTestedInstance()
            ->string($this->testedInstance->getCharset())
                ->isIdenticalTo($this->charset);
    }

    public function testGetDatabase()
    {
        $this
            ->createTestedInstance()
            ->string($this->testedInstance->getDatabase())
                ->isIdenticalTo($this->database);
    }
}
