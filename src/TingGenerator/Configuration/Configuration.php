<?php
/***********************************************************************
 *
 * Ting Generator
 * ==========================================
 *
 * Copyright (C) 2014 CCM Benchmark Group. (http://www.ccmbenchmark.com)
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

namespace CCMBenchmark\TingGenerator\Configuration;

use CCMBenchmark\TingGenerator\Database\ConnectionData;
use CCMBenchmark\TingGenerator\Log\Logger;
use CCMBenchmark\TingGenerator\Configuration\Exception\MissingParameterException;

class Configuration
{
    /**
     * @var array
     */
    private $configurationData;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var mixed
     */
    private $defaultFormatter;

    /**
     * Configuration constructor.
     * @param array $configurationData
     */
    public function __construct(array $configurationData, Logger $logger)
    {
        $this->configurationData = $configurationData;
        $this->logger = $logger;

        $this->defaultFormatter = function ($string) {
            return $string;
        };
    }

    /**
     * @param string $parameterName
     *
     * @throws MissingParameterException
     *
     * @return int|string
     */
    private function getMandatoryParameter($parameterName)
    {
        if (isset($this->configurationData[$parameterName]) === false) {
            throw MissingParameterException::fromMissingParameterInConfiguration(
                'Parameter "' . $parameterName . '" not found in your configuration file.'
            );
        }

        return $this->configurationData[$parameterName];
    }

    /**
     * @param string $parameterName
     *
     * @return mixed
     */
    private function getParameter($parameterName)
    {
        if (isset($this->configurationData[$parameterName]) === false) {
            return null;
        }

        return $this->configurationData[$parameterName];
    }

    /**
     * @throws MissingParameterException
     *
     * @return string
     */
    public function getDataSourceType()
    {
        return (string) $this->getMandatoryParameter('type');
    }

    /**
     * @throws MissingParameterException
     *
     * @return string
     */
    public function getHost()
    {
        return (string) $this->getMandatoryParameter('host');
    }

    /**
     * @throws MissingParameterException
     *
     * @return int
     */
    public function getPort()
    {
        return (int) $this->getMandatoryParameter('port');
    }

    /**
     * @throws MissingParameterException
     *
     * @return string
     */
    public function getDatabaseName()
    {
        return (string) $this->getMandatoryParameter('databaseName');
    }

    /**
     * @throws MissingParameterException
     *
     * @return string
     */
    public function getCharset()
    {
        return (string) $this->getMandatoryParameter('charset');
    }

    /**
     * @throws MissingParameterException
     *
     * @return string
     */
    public function getUsername()
    {
        return (string) $this->getMandatoryParameter('username');
    }

    /**
     * @throws MissingParameterException
     *
     * @return string
     */
    public function getPassword()
    {
        return (string) $this->getMandatoryParameter('password');
    }

    /**
     * @return ConnectionData|null
     */
    public function getConnectionData()
    {
        try {
            return new ConnectionData(
                $this->getDataSourceType(),
                $this->getHost(),
                $this->getUsername(),
                $this->getPassword(),
                $this->getPort(),
                $this->getCharset(),
                $this->getDatabaseName()
            );
        } catch (MissingParameterException $exception) {
            $this->logger->error($exception->getMessage());
            return null;
        }
    }

    /**
     * @return mixed|null
     */
    public function getExcludedTablesFilter()
    {
        $excludedTablesFilter = $this->getParameter('excludedTablesFilter');
        if (is_callable($excludedTablesFilter) === false) {
            $this->logger->warning('Parameter \'excludedTablesFilter\' is defined but must be a callable.');
            return null;
        }

        return $excludedTablesFilter;
    }

    /**
     * @return string
     */
    public function getEntityNamespace()
    {
        return (string) $this->getParameter('entityNamespace');
    }

    /**
     * @return mixed
     */
    public function getEntityNameFormatter()
    {
        $entityNameFormatter = $this->getParameter('entityNameFormatter');
        if (is_callable($entityNameFormatter) === false) {
            $this->logger->warning('Parameter \'entityNameFormatter\' is defined but must be a callable.');
            $entityNameFormatter = $this->defaultFormatter;
        }

        return $entityNameFormatter;
    }

    /**
     * @return string
     */
    public function getEntityDirectory()
    {
        try {
            return (string)$this->getMandatoryParameter('entitiesDirectory');
        } catch (MissingParameterException $exception) {
            $this->logger->error($exception->getMessage());
            return '';
        }
    }

    /**
     * @return string
     */
    public function getRepositoryNamespace()
    {
        return (string) $this->getParameter('repositoryNamespace');
    }

    /**
     * @return mixed
     */
    public function getRepositoryNameFormatter()
    {
        $repositoryNameFormatter = $this->getParameter('repositoryNameFormatter');
        if (is_callable($repositoryNameFormatter) === false) {
            $this->logger->warning('Parameter \'repositoryNameFormatter\' is defined but must be a callable.');
            $repositoryNameFormatter = $this->defaultFormatter;
        }

        return $repositoryNameFormatter;
    }

    /**
     * @return string
     */
    public function getRepositoryDirectory()
    {
        try {
            return (string) $this->getMandatoryParameter('repositoriesDirectory');
        } catch (MissingParameterException $exception) {
            $this->logger->error($exception->getMessage());
            return '';
        }
    }

    /**
     * @return int
     */
    public function getTargetDirectoryRight()
    {
        return (int) $this->getParameter('targetDirectoryRight');
    }
}
