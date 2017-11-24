<?php

namespace CCMBenchmark\TingGenerator\Database;

use CCMBenchmark\Ting\Repository\Metadata;
use CCMBenchmark\Ting\Repository\MetadataInitializer;
use CCMBenchmark\Ting\Repository\Repository as TingRepository;
use CCMBenchmark\Ting\Serializer\SerializerFactoryInterface;

class Repository extends TingRepository implements MetadataInitializer
{
    /**
     * @param SerializerFactoryInterface $serializerFactory
     * @param array $options
     *
     * @return Metadata
     */
    public static function initMetadata(SerializerFactoryInterface $serializerFactory, array $options = [])
    {
        $metadata = new Metadata($serializerFactory);
        $metadata->setConnectionName($options['connection']);
        $metadata->setDatabase($options['database']);

        return $metadata;
    }
}
