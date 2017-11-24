<?php

namespace CCMBenchmark\TingGenerator\Database\MySQL;

use CCMBenchmark\Ting\Exception;
use CCMBenchmark\Ting\Query\QueryException;
use CCMBenchmark\Ting\Repository\HydratorArray;
use CCMBenchmark\TingGenerator\Database\FieldDescription;
use CCMBenchmark\TingGenerator\Database\TableDescription;
use CCMBenchmark\TingGenerator\Database\Repository;
use CCMBenchmark\TingGenerator\Log\Logger;

class TableAnalyzer
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
                $row['Key'] === 'PRI',
                $row['Extra'] === 'auto_increment'
            );
        }

        return new TableDescription($tableName, $tableData);
    }
}
