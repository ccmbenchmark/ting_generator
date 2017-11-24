<?php

namespace CCMBenchmark\TingGenerator\Database\Analyzer;

use CCMBenchmark\TingGenerator\Database\Analyzer\MySQL\TableAnalyzer;
use CCMBenchmark\TingGenerator\Database\Analyzer\MySQL\TypeMapping;
use CCMBenchmark\TingGenerator\Database\RepositoryFactory;
use CCMBenchmark\TingGenerator\Log\Logger;

class AnalyzerFactory
{
    /**
     * @var RepositoryFactory
     */
    private $repositoryFactory;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * AnalyzerFactory constructor.
     * @param RepositoryFactory $repositoryFactory
     * @param Logger $logger
     */
    public function __construct(RepositoryFactory $repositoryFactory, Logger $logger)
    {
        $this->repositoryFactory = $repositoryFactory;
        $this->logger = $logger;
    }

    /**
     * @param string$type
     *
     * @return TableAnalyzerInterface|null
     */
    public function getFromType($type)
    {
        if ($type === 'mysql') {
            return new TableAnalyzer(
                new TypeMapping(),
                $this->logger,
                $this->repositoryFactory->getRepository()
            );
        }

        $this->logger->error('Unknown table analyzer type "' . $type . '"');
        return null;
    }
}
