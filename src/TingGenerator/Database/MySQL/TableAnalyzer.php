<?php

namespace CCMBenchmark\TingGenerator\Database\MySQL;

use CCMBenchmark\TingGenerator\Database\FieldDescription;
use CCMBenchmark\TingGenerator\Database\TableDescription;
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
     * @var \PDO
     */
    private $pdo;

    /**
     * @var string
     */
    private $databaseName;

    const DEFAULT_HOST = '127.0.0.1';

    const DEFAULT_PORT = 3306;

    const DEFAULT_CHARSET = 'utf8';

    /**
     * TableAnalyzer constructor.
     * @param TypeMapping $typeMapping
     * @param Logger $logger
     */
    public function __construct(TypeMapping $typeMapping, Logger $logger)
    {
        $this->typeMapping = $typeMapping;
        $this->logger = $logger;
    }

    /**
     * @param string $userName
     * @param string $password
     * @param string $databaseName
     * @param string $host
     * @param int $port
     * @param string $charset
     *
     * @return $this
     */
    public function connect(
        $userName,
        $password,
        $databaseName,
        $host = self::DEFAULT_HOST,
        $port = self::DEFAULT_PORT,
        $charset = self::DEFAULT_CHARSET
    ) {
        $this->databaseName = $databaseName;

        $host = (string) $host;
        if ($host === '') {
            $host = self::DEFAULT_HOST;
        }

        $port = (int) $port;
        if ($port === 0) {
            $port = self::DEFAULT_PORT;
        }

        $charset = (string) $charset;
        if ($charset === '') {
            $charset = self::DEFAULT_CHARSET;
        }

        $this->pdo = new \PDO(
            sprintf(
                '%s:host=%s;port:%s;dbname=%s;charset=%s',
                'mysql',
                $host,
                $port,
                $this->databaseName,
                $charset
            ),
            $userName,
            $password
        );

        return $this;
    }

    /**
     * @param callable $excludedTablesFilter
     *
     * @return array
     */
    public function getTablesData(callable $excludedTablesFilter = null)
    {
        $query = $this->pdo->query(sprintf('SHOW TABLES FROM %s', $this->databaseName));
        $tablesDataList = [];
        foreach ($query->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $key = 'Tables_in_' . $this->databaseName;

            if ($excludedTablesFilter($row[$key]) === true) {
                continue;
            }

            $tablesDataList[$row[$key]] = $this->getTableData($row[$key]);
        }

        return $tablesDataList;
    }

    /**
     * @param string $tableName
     *
     * @return TableDescription
     */
    private function getTableData($tableName)
    {
        $query = $this
            ->pdo
            ->query(
                sprintf('DESCRIBE `%s`.`%s`', $this->databaseName, (string) $tableName)
            );

        $tableData = [];
        foreach ($query as $row) {
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
