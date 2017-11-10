<?php
/**
 * sudo -E php application.php ting:generate --conf=/var/www/ting_generator/conf.php --mode=3
 */

namespace CCMBenchmark\TingGenerator\Command;

use CCMBenchmark\TingGenerator\Generator\Repository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use CCMBenchmark\TingGenerator\Generator\Entity;
use CCMBenchmark\TingGenerator\FileGeneration\ClassWriter;
use CCMBenchmark\TingGenerator\Infrastructure\StringFormatter;
use CCMBenchmark\TingGenerator\Log\Logger;
use CCMBenchmark\TingGenerator\Configuration\ConfigurationLoader;
use CCMBenchmark\TingGenerator\Database\MySQL\TableAnalyzer;
use CCMBenchmark\TingGenerator\Configuration\Configuration;
use CCMBenchmark\TingGenerator\Database\MySQL\TypeMapping;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\FileGenerator;

class TingGenerateCommand extends Command
{
    const MODE_ONLY_ENTITIES = 1;
    const MODE_ONLY_REPOSITORIES = 2;
    const MODE_ALL = 3;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Entity
     */
    private $entityGenerator;

    /**
     * @var Repository
     */
    private $repositoryGenerator;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var ClassWriter
     */
    private $classWriter;

    /**
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    protected function configure()
    {
        $this
            ->setName('ting:generate')
            ->setDescription('Generate your entities')
            ->addOption(
                'conf',
                null,
                InputOption::VALUE_REQUIRED,
                'Full path to configuration file'
            )
            ->addOption(
                'mode',
                self::MODE_ALL,
                InputOption::VALUE_REQUIRED,
                'Generation mode. By default, generate entities and repositories'
            );
    }

    /**
     * @param InputInterface $input
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     *
     * @return int|null
     */
    private function getGenerationMode(InputInterface $input)
    {
        $generationMode = (int) $input->getOption('mode');
        if (in_array(
            $generationMode,
            [self::MODE_ONLY_ENTITIES, self::MODE_ONLY_REPOSITORIES, self::MODE_ALL]
        ) === false) {
            return null;
        }

        return $generationMode;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \Zend\Code\Generator\Exception\InvalidArgumentException
     * @throws \Zend\Code\Generator\Exception\RuntimeException
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger = new Logger($output);
        $this->entityGenerator = new Entity(new ClassGenerator(), $this->logger, new StringFormatter());
        $this->repositoryGenerator = new Repository(new ClassGenerator(), $this->logger, new StringFormatter());
        $this->classWriter = new ClassWriter(new FileGenerator(), $this->logger);

        $confOption = $input->getOption('conf');
        $this->configuration = $this->getConfiguration($confOption);
        if ($this->configuration === null) {
            $this->logger->error('Configuration file not found: ' . $confOption);
            return 1;
        }

        $modeOption = $this->getGenerationMode($input);
        if ($modeOption === null) {
            $this->logger->error('Invalid mode: ' . $modeOption);
            return 1;
        }

        $tablesData = $this->getTablesData();
        if ($tablesData === null) {
            $this->logger->error('Unable to read data source and retrieve table data');
            return 1;
        }

        $entityNameFormatter = $this->configuration->getEntityNameFormatter();
        $repositoryNameFormatter = $this->configuration->getRepositoryNameFormatter();

        foreach ($tablesData as $tableName => $tableData) {
            $this->logger->info('----------------------------------------------');

            $entityName = $entityNameFormatter($tableName);
            $entityNamespace = $this->configuration->getEntityNamespace();
            $this->generateEntity($entityName, $entityNamespace, $tableData);

            $repositoryName = $repositoryNameFormatter($tableName);
            $this->generateRepository(
                $repositoryName,
                $this->configuration->getRepositoryNamespace(),
                $tableData,
                $entityNamespace . '\\' . $entityName
            );
        }

        return 1;
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
        $this->logger->info('Generate entity: ' . $entityName);

        $classGenerator = $this
            ->entityGenerator
            ->generateEntityCode($entityName, $entityNamespace, $tableData)
            ->getClassGenerator();

        return $this->writeClass($entityName, $classGenerator, $this->configuration->getEntityDirectory());
    }

    private function generateRepository(
        $repositoryName,
        $repositoryNamespace,
        array $tableData,
        $entityFullQualifiedName
    ) {
        $this->logger->info('Generate repository: ' . $repositoryName);

        $classGenerator = $this
            ->repositoryGenerator
            ->generateRepositoryCode(
                $repositoryName,
                $repositoryNamespace,
                $tableData,
                $entityFullQualifiedName
            )->getClassGenerator();

        return $this->writeClass(
            $repositoryName,
            $classGenerator,
            $this->configuration->getRepositoryDirectory()
        );
    }

    /**
     * @param string $entityName
     * @param ClassGenerator $classGenerator
     * @param string $targetDirectory
     *
     * @throws \Zend\Code\Generator\Exception\InvalidArgumentException
     * @throws \Zend\Code\Generator\Exception\RuntimeException
     *
     * @return bool Return true on success, false on failure.
     */
    private function writeClass($entityName, $classGenerator, $targetDirectory)
    {
        $this->logger->info('Writing...');

        if ($targetDirectory === '') {
            $this
                ->logger
                ->error('You must specify a repository for generated entities. See entitiesDirectory in conf.');
            return false;
        }

        $this->logger->info('... in directory: ' . $targetDirectory);

        return $this->classWriter->write($entityName, $classGenerator, $targetDirectory);
    }
}
