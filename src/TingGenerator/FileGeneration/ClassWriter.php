<?php

namespace CCMBenchmark\TingGenerator\FileGeneration;

use CCMBenchmark\TingGenerator\Log\Logger;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\Exception\RuntimeException;
use Zend\Code\Generator\FileGenerator;

class ClassWriter
{
    /**
     * @var FileGenerator
     */
    private $baseFileGenerator;

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
        $this->baseFileGenerator = $fileGenerator;
        $this->logger = $logger;
    }

    /**
     * FileGenerator object must be cleaned before each write.
     * Else it could write a class written previously.
     *
     * @return $this
     */
    private function initializeFileGenerator()
    {
        $this->fileGenerator = clone $this->baseFileGenerator;

        return $this;
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
        $this->initializeFileGenerator();

        $className = (string) $className;
        $targetDirectory = (string) $targetDirectory;

        if (is_dir($targetDirectory) === false) {
            $this->logger->info('Creating directory: ' . $targetDirectory);
            if (mkdir($targetDirectory, 0777, true) === false) {
                $this->logger->error('Unable to write directory: ' . $targetDirectory);
                return false;
            }
        }

        $filename = $targetDirectory . '/' . $className . '.php';
        $this->fileGenerator->setFilename($filename);
        $this->fileGenerator->setClass($classGenerator);

        try {
            $this->fileGenerator->write();
        } catch (RuntimeException $exception) {
            $this->logger->error('Unable to write: ' . $filename . '. Error: ' . $exception->getMessage());
            return false;
        }

        $this->logger->info('File written in: ' . $filename);

        return true;
    }
}
