<?php

namespace CCMBenchmark\TingGenerator\Configuration;

use CCMBenchmark\TingGenerator\Log\Logger;

class ConfigurationLoader
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * ConfigurationLoader constructor.
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $filename
     *
     * @return Configuration|null
     */
    public function loadFromFile($filename)
    {
        $filename = (string) $filename;

        if (file_exists($filename) === false) {
            return null;
        }

        return new Configuration(
            require_once $filename,
            $this->logger
        );
    }
}
