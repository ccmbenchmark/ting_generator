<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/autoload.php';

use CCMBenchmark\TingGenerator\Log\Logger;
use CCMBenchmark\TingGenerator\Configuration\ConfigurationLoader;
use CCMBenchmark\TingGenerator\Database\MySQL\TableAnalyzer;
use CCMBenchmark\TingGenerator\Configuration\Configuration;
use CCMBenchmark\TingGenerator\Database\MySQL\TypeMapping;

class GenerateEntity
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * GenerateEntity constructor.
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function execute()
    {
        $configuration = $this->getConfiguration(__DIR__ . '/conf.php');
        if ($configuration === null) {
            $this->logger->error('Configuration file not found');
            exit;
        }

        $tablesData = $this->getTablesData($configuration);
        if ($tablesData === null) {
            $this->logger->error('Unable to read data source and retrieve table data');
            exit;
        }
    }

    /**
     * @param string $confFilename
     *
     * @return \CCMBenchmark\TingGenerator\Configuration\Configuration|null
     */
    private function getConfiguration($confFilename)
    {
        $configurationLoader = new ConfigurationLoader($this->logger);
        return $configurationLoader->loadFromFile($confFilename);
    }

    /**
     * @param Configuration $configuration
     *
     * @return array
     */
    private function getTablesData(Configuration $configuration)
    {
        $tableAnalyzer = new TableAnalyzer(new TypeMapping($this->logger), $this->logger);
        return $tableAnalyzer
            ->connect(
                $configuration->getUsername(),
                $configuration->getPassword(),
                $configuration->getDatabaseName(),
                $configuration->getHost(),
                $configuration->getPort(),
                $configuration->getCharset()
            )
            ->getTablesData($configuration->getExcludedTablesFilter());
    }
}

$generateEntity = new GenerateEntity(new Logger());
$generateEntity->execute();