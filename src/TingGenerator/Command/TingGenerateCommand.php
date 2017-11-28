<?php
/**
 * sudo -E php application.php ting:generate --conf=/var/www/ting_generator/sample/conf.php --mode=3
 */

namespace CCMBenchmark\TingGenerator\Command;

use CCMBenchmark\Ting\Services;
use CCMBenchmark\TingGenerator\Database\Analyzer\AnalyzerFactory;
use CCMBenchmark\TingGenerator\Database\RepositoryFactory;
use CCMBenchmark\TingGenerator\Database\TableDescription;
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
use CCMBenchmark\TingGenerator\Database\Analyzer\MySQL\TableAnalyzer;
use CCMBenchmark\TingGenerator\Configuration\Configuration;
use CCMBenchmark\TingGenerator\Database\Analyzer\MySQL\TypeMapping;
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
     * @var int
     */
    private $generationMode;

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
                null,
                InputOption::VALUE_REQUIRED,
                'Generation mode. By default, generate entities and repositories'
            );
    }

    /**
     * @param InputInterface $input
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     *
     * @return int
     */
    private function getGenerationMode(InputInterface $input)
    {
        $generationMode = (int) $input->getOption('mode');
        if (in_array(
            $generationMode,
            [self::MODE_ONLY_ENTITIES, self::MODE_ONLY_REPOSITORIES, self::MODE_ALL]
        ) === false) {
            return self::MODE_ALL;
        }

        return $generationMode;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws \Pimple\Exception\UnknownIdentifierException
     * @throws \RuntimeException
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

        $this->generationMode = $this->getGenerationMode($input);

        $tablesData = $this->getTablesData();
        if ($tablesData === null) {
            $this->logger->error('Unable to read data source and retrieve table data');
            return 1;
        }

        $entityNameFormatter = $this->configuration->getEntityNameFormatter();
        $repositoryNameFormatter = $this->configuration->getRepositoryNameFormatter();

        /**
         * @var TableDescription $tableDescription
         */
        foreach ($tablesData as $tableDescription) {
            $this->logger->info('----------------------------------------------');

            $success = $this->generate($tableDescription, $entityNameFormatter, $repositoryNameFormatter);
            if ($success === false) {
                return 1;
            }
        }

        return 0;
    }

    /**
     * @param TableDescription $tableDescription
     * @param $entityNameFormatter
     * @param $repositoryNameFormatter
     *
     * @throws \Zend\Code\Generator\Exception\InvalidArgumentException
     * @throws \Zend\Code\Generator\Exception\RuntimeException
     *
     * @return boolean
     */
    private function generate(TableDescription $tableDescription, $entityNameFormatter, $repositoryNameFormatter)
    {
        $entityName = $entityNameFormatter($tableDescription->getName());
        $entityNamespace = $this->configuration->getEntityNamespace();

        $success = true;
        if ($this->shouldGenerateEntity() === true) {
            $success = $this->generateEntity($entityName, $entityNamespace, $tableDescription);
        }

        if ($success === false) {
            return false;
        }

        if ($this->shouldGenerateRepository() === true) {
            $repositoryName = $repositoryNameFormatter($tableDescription->getName());
            return $this->generateRepository(
                $repositoryName,
                $this->configuration->getRepositoryNamespace(),
                $tableDescription,
                $entityNamespace . '\\' . $entityName
            );
        }

        return true;
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
     * @throws \Pimple\Exception\UnknownIdentifierException
     * @throws \RuntimeException
     *
     * @return array
     */
    private function getTablesData()
    {
        $connectionData = $this->configuration->getConnectionData();
        if ($connectionData === null) {
            return null;
        }

        $services = new Services();
        $repositoryFactory = new RepositoryFactory(
            $connectionData,
            $services->get('ConnectionPool'),
            $services->get('MetadataRepository'),
            $services->get('RepositoryFactory')
        );

        $analyzerFactory = new AnalyzerFactory($repositoryFactory, $this->logger);
        $analyzer = $analyzerFactory->getFromType($connectionData->getType());
        if ($analyzer === null) {
            return null;
        }

        return $analyzer
            ->getTablesData($connectionData->getDatabase(), $this->configuration->getExcludedTablesFilter());
    }

    /**
     * Generate source code and write entity.
     *
     * @param string $entityName
     * @param string $entityNamespace
     * @param TableDescription $tableDescription
     *
     * @throws \Zend\Code\Generator\Exception\InvalidArgumentException
     * @throws \Zend\Code\Generator\Exception\RuntimeException
     *
     * @return bool Return true on success, false on failure.
     */
    private function generateEntity($entityName, $entityNamespace, TableDescription $tableDescription)
    {
        $this->logger->info('Generate entity: ' . $entityName);

        $classGenerator = $this
            ->entityGenerator
            ->generateEntityCode($entityName, $entityNamespace, $tableDescription->getFieldsDescription())
            ->getClassGenerator();

        return $this->writeClass($entityName, $classGenerator, $this->configuration->getEntityDirectory());
    }

    /**
     * @param string $repositoryName
     * @param string $repositoryNamespace
     * @param TableDescription $tableDescription
     * @param string $entityFullQualifiedName
     *
     * @throws \Zend\Code\Generator\Exception\InvalidArgumentException
     * @throws \Zend\Code\Generator\Exception\RuntimeException
     *
     * @return bool
     */
    private function generateRepository(
        $repositoryName,
        $repositoryNamespace,
        TableDescription $tableDescription,
        $entityFullQualifiedName
    ) {
        $this->logger->info('Generate repository: ' . $repositoryName);

        $classGenerator = $this
            ->repositoryGenerator
            ->generateRepositoryCode(
                $repositoryName,
                $repositoryNamespace,
                $tableDescription,
                $entityFullQualifiedName
            )->getClassGenerator();

        return $this->writeClass(
            $repositoryName,
            $classGenerator,
            $this->configuration->getRepositoryDirectory()
        );
    }

    /**
     * @param string $className
     * @param ClassGenerator $classGenerator
     * @param string $targetDirectory
     *
     * @throws \Zend\Code\Generator\Exception\InvalidArgumentException
     * @throws \Zend\Code\Generator\Exception\RuntimeException
     *
     * @return bool Return true on success, false on failure.
     */
    private function writeClass($className, $classGenerator, $targetDirectory)
    {
        if ($targetDirectory === '') {
            $this
                ->logger
                ->error('You must specify a target directory for generated classes.');
            return false;
        }

        $this->logger->info('Writing class in directory: ' . $targetDirectory);

        return $this
            ->classWriter
            ->write($className, $classGenerator, $targetDirectory, $this->configuration->getTargetDirectoryRight());
    }

    /**
     * @return bool
     */
    private function shouldGenerateEntity()
    {
        return $this->generationMode === self::MODE_ALL || $this->generationMode === self::MODE_ONLY_ENTITIES;
    }

    /**
     * @return bool
     */
    private function shouldGenerateRepository()
    {
        return $this->generationMode === self::MODE_ALL || $this->generationMode === self::MODE_ONLY_REPOSITORIES;
    }
}
