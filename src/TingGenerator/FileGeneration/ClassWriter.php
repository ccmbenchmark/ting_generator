<?php

namespace CCMBenchmark\TingGenerator\FileGeneration;

use CCMBenchmark\TingGenerator\Log\Logger;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\FileGenerator;

class ClassWriter
{
    /**
     * @var FileGenerator
     */
    private $fileGenerator;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * ClassWriter constructor.
     * @param FileGenerator $fileGenerator
     * @param Logger $logger
     */
    public function __construct(FileGenerator $fileGenerator, Logger $logger)
    {
        $this->fileGenerator = $fileGenerator;
        $this->logger = $logger;
    }

    /**
     * @param string $className
     * @param ClassGenerator $classGenerator
     * @param string $targetDirectory
     *
     * @throws \Zend\Code\Generator\Exception\InvalidArgumentException
     * @throws \Zend\Code\Generator\Exception\RuntimeException
     *
     * @return bool
     */
    public function write($className, ClassGenerator $classGenerator, $targetDirectory)
    {
        $className = (string) $className;
        $targetDirectory = (string) $targetDirectory;

        if (is_dir($targetDirectory) === false) {
            $this->logger->info('Creating directory: ' . $targetDirectory);
            if (mkdir($targetDirectory, 0777, true) === false) {
                $this->logger->error('Unable to write directory: ' . $targetDirectory);
                return false;
            }
        }

        $this->fileGenerator->setClass($classGenerator);

        $filename = $targetDirectory . '/' . $className . '.php';
        if (file_put_contents($filename, $this->fileGenerator->generate()) === false) {
            $this->logger->error('Error when writing "' . $filename . '"');
            return false;
        }

        $this->logger->info('File written in: ' . $filename);

        return true;
    }
}
