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

namespace CCMBenchmark\TingGenerator\Database\Analyzer\MySQL;

use CCMBenchmark\Ting\Exception;
use CCMBenchmark\Ting\Query\QueryException;
use CCMBenchmark\Ting\Repository\HydratorArray;
use CCMBenchmark\TingGenerator\Database\FieldDescription;
use CCMBenchmark\TingGenerator\Database\Analyzer\TableAnalyzerInterface;
use CCMBenchmark\TingGenerator\Database\TableDescription;
use CCMBenchmark\TingGenerator\Database\Repository;
use CCMBenchmark\TingGenerator\Log\Logger;

class TableAnalyzer implements TableAnalyzerInterface
{
    /**
     * @var TypeMapping
     */
    private $typeMapping;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * TableAnalyzer constructor.
     * @param TypeMapping $typeMapping
     * @param Logger $logger
     * @param Repository $repository
     */
    public function __construct(TypeMapping $typeMapping, Logger $logger, Repository $repository)
    {
        $this->typeMapping = $typeMapping;
        $this->logger = $logger;
        $this->repository = $repository;
    }

    /**
     * @param string $databaseName
     * @param callable $excludedTablesFilter
     *
     * @return array
     */
    public function getTablesData($databaseName, callable $excludedTablesFilter = null)
    {
        $tablesDataList = [];
        $query = $this->repository->getQuery(sprintf('SHOW TABLES FROM %s', $databaseName));
        try {
            $result = $query->query($this->repository->getCollection(new HydratorArray()));
        } catch (QueryException $exception) {
            $this->logger->error($exception->getMessage());
            return $tablesDataList;
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            return $tablesDataList;
        }

        foreach ($result as $row) {
            $tableName = current($row);
            if ($excludedTablesFilter($tableName) === true) {
                continue;
            }

            $tableData = $this->getTableData($databaseName, $tableName);
            if ($tableData === null) {
                continue;
            }

            $tablesDataList[$tableName] = $tableData;

        }

        return $tablesDataList;
    }

    /**
     * @param string $databaseName
     * @param string $tableName
     *
     * @return TableDescription|null
     */
    private function getTableData($databaseName, $tableName)
    {
        $query = $this
            ->repository
            ->getQuery(sprintf('DESCRIBE `%s`.`%s`', $databaseName, (string) $tableName));

        try {
            $result = $query->query($this->repository->getCollection(new HydratorArray()));
        } catch (QueryException $exception) {
            $this->logger->error($exception->getMessage());
            return null;
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            return null;
        }

        $tableData = [];
        foreach ($result as $row) {
            $tableData[] = new FieldDescription(
                $this->typeMapping->getFromMysqlType($row['Type']),
                $row['Field'],
                $this->isFieldPrimaryKey($row),
                $this->isFieldAutoIncrement($row)
            );
        }

        return new TableDescription($tableName, $tableData);
    }

    /**
     * @param array $row
     *
     * @return bool
     */
    private function isFieldPrimaryKey(array $row)
    {
        return $row['Key'] === 'PRI';
    }

    /**
     * @param array $row
     *
     * @return bool
     */
    private function isFieldAutoIncrement(array $row)
    {
        return $row['Extra'] === 'auto_increment';
    }
}
