<?php

namespace CCMBenchmark\TingGenerator\Database;

use CCMBenchmark\Ting\ConnectionPoolInterface;
use CCMBenchmark\Ting\MetadataRepository;
use CCMBenchmark\Ting\Repository\RepositoryFactory as TingRepositoryFactory;

class RepositoryFactory
{
    /**
     * @var ConnectionData
     */
    private $connectionData;

    /**
     * @var ConnectionPoolInterface
     */
    private $connectionPool;

    /**
     * @var MetadataRepository
     */
    private $metadataRepository;

    /**
     * @var TingRepositoryFactory
     */
    private $tingRepositoryFactory;

    /**
     * RepositoryFactory constructor.
     * @param ConnectionData $connectionData
     * @param ConnectionPoolInterface $connectionPool
     * @param MetadataRepository $metadataRepository
     * @param TingRepositoryFactory $tingRepositoryFactory
     */
    public function __construct(
        ConnectionData $connectionData,
        ConnectionPoolInterface $connectionPool,
        MetadataRepository $metadataRepository,
        TingRepositoryFactory $tingRepositoryFactory
    ) {
        $this->connectionData = $connectionData;
        $this->connectionPool = $connectionPool;
        $this->metadataRepository = $metadataRepository;
        $this->tingRepositoryFactory = $tingRepositoryFactory;

        $this->configureConnectionPool();
        
        $this->metadataRepository->batchLoadMetadata(
            'CCMBenchmark\TingGenerator\Database',
            __DIR__ . '/../Database/Repository.php',
            [
                'CCMBenchmark\TingGenerator\Database\Repository' => [
                    'connection' => 'main',
                    'database' => $this->connectionData->getDatabase()
                ]
            ]
        );
    }

    /**
     * @return $this
     */
    private function configureConnectionPool()
    {
        $this
            ->connectionPool
            ->setConfig([
                'main' => [
                    'namespace' => $this->getNamespaceFromConnectionData(),
                    'master' => [
                        'host'     => $this->connectionData->getHost(),
                        'user'     => $this->connectionData->getUserName(),
                        'password' => $this->connectionData->getPassword(),
                        'port'     => $this->connectionData->getPort(),
                        'charset'  => $this->connectionData->getCharset()
                    ]
                ]
            ]);

        return $this;
    }

    /**
     * @return string
     */
    private function getNamespaceFromConnectionData()
    {
        if ($this->connectionData->getType() === 'pgsql') {
            return '\CCMBenchmark\Ting\Driver\Pgsql';
        }

        return '\CCMBenchmark\Ting\Driver\Mysqli';
    }

    /**
     * @return Repository
     */
    public function getRepository()
    {
        return $this->tingRepositoryFactory->get('CCMBenchmark\TingGenerator\Database\Repository');
    }
}
