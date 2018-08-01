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

use CCMBenchmark\TingGenerator\Configuration\Exception\MissingParameterException;
use CCMBenchmark\TingGenerator\Database\ConnectionData;
use mageekguy\atoum;
use Psr\Log\LoggerInterface;

/**
 * Class Configuration
 * @package tests\units\CCMBenchmark\TingGenerator\Configuration
 * @tags Configuration
 */
class Configuration extends atoum
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param string $method
     */
    public function beforeTestMethod($method)
    {
        $this->logger = new \mock\Psr\Log\LoggerInterface();
    }

    public function testGetDataSourceTypeReturnSourceType()
    {
        $this
            ->given($sourceType = uniqid('sourceType'))
            ->and($this->newTestedInstance(['type' => $sourceType], $this->logger))
            ->string($this->testedInstance->getDataSourceType())
                ->isIdenticalTo($sourceType);
    }

    public function testGetHostReturnHost()
    {
        $this
            ->given($host = uniqid('host'))
            ->and($this->newTestedInstance(['host' => $host], $this->logger))
            ->string($this->testedInstance->getHost())
                ->isIdenticalTo($host);
    }

    public function testGetPortReturnPort()
    {
        $this
            ->given($port = rand(0, PHP_INT_MAX))
            ->and($this->newTestedInstance(['port' => $port], $this->logger))
            ->integer($this->testedInstance->getPort())
                ->isIdenticalTo($port);
    }

    public function testGetDatabaseNameReturnDatabaseName()
    {
        $this
            ->given($databaseName = uniqid('databaseName'))
            ->and($this->newTestedInstance(['databaseName' => $databaseName], $this->logger))
            ->string($this->testedInstance->getDatabaseName())
                ->isIdenticalTo($databaseName);
    }

    public function testGetCharsetReturnCharset()
    {
        $this
            ->given($charset = uniqid('charset'))
            ->and($this->newTestedInstance(['charset' => $charset], $this->logger))
            ->string($this->testedInstance->getCharset())
                ->isIdenticalTo($charset);
    }

    public function testGetUsernameReturnUsername()
    {
        $this
            ->given($username = uniqid('username'))
            ->and($this->newTestedInstance(['username' => $username], $this->logger))
            ->string($this->testedInstance->getUsername())
                ->isIdenticalTo($username);
    }

    public function testGetPasswordReturnPassword()
    {
        $this
            ->given($password = uniqid('password'))
            ->and($this->newTestedInstance(['password' => $password], $this->logger))
            ->string($this->testedInstance->getPassword())
                ->isIdenticalTo($password);
    }

    public function testGetConnectionDataReturnConnectionData()
    {
        $this
            ->given(
                $configurationData = [
                    'type' => uniqid('type'),
                    'host' => uniqid('host'),
                    'port' => rand(0, PHP_INT_MAX),
                    'databaseName' => uniqid('auth_ccm_net'),
                    'charset' => uniqid('utf8'),
                    'username' => uniqid('mysql'),
                    'password' => uniqid('p455w0rd')
                ]
            )
            ->and(
                $connectionData = new ConnectionData(
                    $configurationData['type'],
                    $configurationData['host'],
                    $configurationData['username'],
                    $configurationData['password'],
                    $configurationData['port'],
                    $configurationData['charset'],
                    $configurationData['databaseName']
                )
            )
            ->and($this->newTestedInstance($configurationData, $this->logger))
                ->object($this->testedInstance->getConnectionData())
                    ->isEqualTo($connectionData);
    }

    public function testGetConnectionDataReturnNullOnMissingParameter()
    {
        $this
            ->given(
                $configurationData = [
                    'type' => uniqid('type'),
                    'host' => uniqid('host'),
                    'port' => rand(0, PHP_INT_MAX),
                    'databaseName' => uniqid('auth_ccm_net'),
                    'charset' => uniqid('utf8'),
                    'username' => uniqid('mysql'),
                    'password' => uniqid('p455w0rd')
                ]
            )
            ->and($configurationData = array_splice($configurationData, 0, rand(1, count($configurationData)-1)))
            ->and($this->newTestedInstance($configurationData, $this->logger))
            ->variable($this->testedInstance->getConnectionData())
                ->isNull()
            ->mock($this->logger)
                ->call('error')
                    ->once();
    }

    public function testGetExcludedTablesFilterReturnFilter()
    {
        $this
            ->given(
                $excludedTablesFilter = function () {
                    return rand(0, 1) === 1;
                }
            )
            ->and($this->newTestedInstance(['excludedTablesFilter' =>$excludedTablesFilter], $this->logger))
            ->variable($this->testedInstance->getExcludedTablesFilter())
                ->isCallable();
    }

    public function testGetExcludedTablesFilterReturnNullIfParameterIsNotCallable()
    {
        $this
            ->given($this->function->is_callable = false)
            ->and($this->newTestedInstance([], $this->logger))
            ->variable($this->testedInstance->getExcludedTablesFilter())
                ->isNull()
            ->mock($this->logger)
                ->call('warning')
                    ->once();
    }

    public function testGetEntityNamespaceReturnNamespace()
    {
        $this
            ->given($entityNamespace = uniqid('entityNamespace'))
            ->and($this->newTestedInstance(['entityNamespace' => $entityNamespace], $this->logger))
            ->variable($this->testedInstance->getEntityNamespace())
                ->isIdenticalTo($entityNamespace);
    }

    public function testGetEntityNameFormatterReturnFormatter()
    {
        $this
            ->given(
                $entityNameFormatter = function () {
                    return uniqid('string');
                }
            )
            ->and($this->newTestedInstance(['entityNameFormatter' => $entityNameFormatter], $this->logger))
            ->variable($this->testedInstance->getEntityNameFormatter())
                ->isEqualTo($entityNameFormatter);
    }

    public function testGetEntityDirectoryReturnEntityDirectory()
    {
        $this
            ->given($entityDirectory = uniqid('entityDirectory'))
            ->and($this->newTestedInstance(['entityDirectory' => $entityDirectory], $this->logger))
            ->string($this->testedInstance->getEntityDirectory())
                ->isIdenticalTo($entityDirectory);
    }

    public function testGetEntityDirectoryReturnEmptyStringIfParameterIsMissing()
    {
        $this
            ->given($this->newTestedInstance([], $this->logger))
            ->string($this->testedInstance->getEntityDirectory())
                ->isIdenticalTo('')
            ->mock($this->logger)
                ->call('error')
                    ->once();
    }

    public function testGetRepositoryNamespaceReturnRepositoryNamespace()
    {
        $this
            ->given($repositoryNamespace = uniqid('repositoryNamespace'))
            ->and($this->newTestedInstance(['repositoryNamespace' => $repositoryNamespace], $this->logger))
            ->string($this->testedInstance->getRepositoryNamespace())
                ->isIdenticalTo($repositoryNamespace);
    }

    public function testGetRepositoryNameFormatterReturnRepositoryNameFormatter()
    {
        $this
            ->given(
                $repositoryNameFormatter = function () {
                    return uniqid('string');
                }
            )
            ->and($this->newTestedInstance(['repositoryNameFormatter' => $repositoryNameFormatter], $this->logger))
            ->variable($this->testedInstance->getRepositoryNameFormatter())
                ->isEqualTo($repositoryNameFormatter);
    }

    public function testGetRepositoryDirectoryReturnRepositoryDirectory()
    {
        $this
            ->given($repositoryDirectory = uniqid('repositoryDirectory'))
            ->and($this->newTestedInstance(['repositoryDirectory' => $repositoryDirectory], $this->logger))
            ->string($this->testedInstance->getRepositoryDirectory())
                ->isIdenticalTo($repositoryDirectory);
    }

    public function testGetRepositoryDirectoryReturnEmptyStringIfParameterIsMissing()
    {
        $this
            ->given($this->newTestedInstance([], $this->logger))
            ->string($this->testedInstance->getRepositoryDirectory())
                ->isIdenticalTo('')
            ->mock($this->logger)
                ->call('error')
                    ->once();
    }

    public function testGetTargetDirectoryRight()
    {
        $this
            ->given($targetDirectoryRight = rand(0, PHP_INT_MAX))
            ->and($this->newTestedInstance(['targetDirectoryRight' => $targetDirectoryRight], $this->logger))
            ->integer($this->testedInstance->getTargetDirectoryRight())
                ->isIdenticalTo($targetDirectoryRight);
    }
}
