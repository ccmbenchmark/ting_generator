<?php

namespace CCMBenchmark\TingGenerator;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/autoload.php';

use CCMBenchmark\TingGenerator\Entity\Generator;
use CCMBenchmark\TingGenerator\Infrastructure\StringFormatter;
use CCMBenchmark\TingGenerator\Log\Logger;
use CCMBenchmark\TingGenerator\Configuration\ConfigurationLoader;
use CCMBenchmark\TingGenerator\Database\MySQL\TableAnalyzer;
use CCMBenchmark\TingGenerator\Configuration\Configuration;
use CCMBenchmark\TingGenerator\Database\MySQL\TypeMapping;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\FileGenerator;

class GenerateEntity
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Generator
     */
    private $entityGenerator;

    /**
     * @var mixed|null
     */
    private $entityNameFormatter;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var FileGenerator
     */
    private $fileGenerator;

    /**
     * GenerateEntity constructor.
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @throws \Zend\Code\Generator\Exception\InvalidArgumentException
     * @throws \Zend\Code\Generator\Exception\RuntimeException
     */
    public function execute()
    {
        $this->configuration = $this->getConfiguration(__DIR__ . '/conf.php');
        if ($this->configuration === null) {
            $this->logger->error('Configuration file not found');
            exit;
        }

        $tablesData = $this->getTablesData();
        if ($tablesData === null) {
            $this->logger->error('Unable to read data source and retrieve table data');
            exit;
        }

        $this->fileGenerator = new FileGenerator();
        $this->entityGenerator = new Generator(new ClassGenerator(), new Logger(), new StringFormatter());
        $this->entityNameFormatter = $this->configuration->getEntityNameFormatter();

        foreach ($tablesData as $tableName => $tableData) {
            $this->logger->info('----------------------------------------------');
            $this->generateEntity($tableName, $this->configuration->getEntityNamespace(), $tableData);
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
     * @return array
     */
    private function getTablesData()
    {
        $tableAnalyzer = new TableAnalyzer(new TypeMapping(), $this->logger);
        return $tableAnalyzer
            ->connect(
                $this->configuration->getUsername(),
                $this->configuration->getPassword(),
                $this->configuration->getDatabaseName(),
                $this->configuration->getHost(),
                $this->configuration->getPort(),
                $this->configuration->getCharset()
            )
            ->getTablesData($this->configuration->getExcludedTablesFilter());
    }

    /**
     * Generate source code and write entity.
     *
     * @param string $entityName
     * @param string $entityNamespace
     * @param array $tableData
     *
     * @throws \Zend\Code\Generator\Exception\InvalidArgumentException
     * @throws \Zend\Code\Generator\Exception\RuntimeException
     *
     * @return bool Return true on success, false on failure.
     */
    private function generateEntity($entityName, $entityNamespace, array $tableData)
    {
        $entityNameFormatted = $entityName;
        if ($this->entityNameFormatter !== null) {
            $formatter = $this->entityNameFormatter;
            $entityNameFormatted = $formatter($entityName);
        }

        $this->logger->info('Generate entity: ' . $entityNameFormatted);

        $classGenerator = $this
            ->entityGenerator
                ->generateEntityCode($entityNameFormatted, $entityNamespace, $tableData)
                ->getClassGenerator();

        return $this->writeEntity($entityNameFormatted, $classGenerator);
    }

    /**
     * @param string $entityName
     * @param ClassGenerator $classGenerator
     *
     * @throws \Zend\Code\Generator\Exception\InvalidArgumentException
     * @throws \Zend\Code\Generator\Exception\RuntimeException
     *
     * @return bool Return true on success, false on failure.
     */
    private function writeEntity($entityName, $classGenerator)
    {
        $this->logger->info('Writing...');

        $entityDirectory = $this->configuration->getEntityDirectory();
        if ($entityDirectory === '') {
            $this
                ->logger
                    ->error('You must specify a repository for generated entities. See entitiesDirectory in conf.');
            return false;
        }

        $this->logger->info('... in directory: ' . $entityDirectory);

        if (is_dir($entityDirectory) === false) {
            $this->logger->info('Creating directory: ' . $entityDirectory);
            if (mkdir($entityDirectory, 0777, true) === false) {
                $this->logger->error('Unable to write directory: ' . $entityDirectory);
                return false;
            }
        }

        $fileGenerator = new FileGenerator();
        $fileGenerator->setClass($classGenerator);

        $filename = $entityDirectory . '/' . $entityName . '.php';
        if (file_put_contents($filename, $fileGenerator->generate()) === false) {
            $this->logger->error('Error when writing "' . $filename . '"');
            return false;
        }

        $this->logger->info('Entity written in: ' . $filename);

        return true;
    }
}

$generateEntity = new GenerateEntity(new Logger());
$generateEntity->execute();