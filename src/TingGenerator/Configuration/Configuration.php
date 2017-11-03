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

use CCMBenchmark\TingGenerator\Log\Logger;

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
     * Configuration constructor.
     * @param array $configurationData
     */
    public function __construct(array $configurationData, Logger $logger)
    {
        $this->configurationData = $configurationData;
        $this->logger = $logger;
    }

    /**
     * @param string $parameterName
     *
     * @return mixed
     */
    private function getMandatoryParameter($parameterName)
    {
        if (isset($this->configurationData[$parameterName]) === false) {
            $this->logger->error('Missing \'' . $parameterName . '\' in configuration');
            return null;
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
     * @return string
     */
    public function getDataSourceType()
    {
        return (string) $this->getMandatoryParameter('type');
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return (string) $this->getMandatoryParameter('host');
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return (string) $this->getMandatoryParameter('port');
    }

    /**
     * @return string
     */
    public function getDatabaseName()
    {
        return (string) $this->getMandatoryParameter('databaseName');
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return (string) $this->getMandatoryParameter('charset');
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return (string) $this->getMandatoryParameter('username');
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return (string) $this->getMandatoryParameter('password');
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
     * @return mixed|null
     */
    public function getEntityNameFormatter()
    {
        $entityNameFormatter = $this->getParameter('entityNameFormatter');
        if (is_callable($entityNameFormatter) === false) {
            $this->logger->warning('Parameter \'entityNameFormatter\' is defined but must be a callable.');
            return null;
        }

        return $entityNameFormatter;
    }
}
