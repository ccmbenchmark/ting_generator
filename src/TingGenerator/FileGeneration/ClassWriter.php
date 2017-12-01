<?php
/***********************************************************************
 *
 * Ting Generator
 * ==========================================
 *
 * Copyright (C) 2017 CCM Benchmark Group. (http://www.ccmbenchmark.com)
 *
 ***********************************************************************
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you
 * may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
 * implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 **********************************************************************/

namespace CCMBenchmark\TingGenerator\FileGeneration;

use CCMBenchmark\TingGenerator\Log\Logger;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\Exception\RuntimeException;
use Zend\Code\Generator\FileGenerator;

class ClassWriter
{
    const DEFAULT_RIGHT = 0777;

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
     * @param int $targetDirectoryRight
     *
     * @throws \Zend\Code\Generator\Exception\InvalidArgumentException
     * @throws \Zend\Code\Generator\Exception\RuntimeException
     *
     * @return bool
     */
    public function write(
        $className,
        ClassGenerator $classGenerator,
        $targetDirectory,
        $targetDirectoryRight = self::DEFAULT_RIGHT
    ) {
        $this->initializeFileGenerator();

        $className = (string) $className;
        $targetDirectory = (string) $targetDirectory;

        if (is_dir($targetDirectory) === false
            && $this->writeTargetDirectory($targetDirectory, $targetDirectoryRight) === false
        ) {
            return false;
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

        return true;
    }

    /**
     * @param string $targetDirectory
     * @param int $targetDirectoryRight
     *
     * @return bool
     */
    private function writeTargetDirectory($targetDirectory, $targetDirectoryRight)
    {
        $targetDirectoryRight = (int) $targetDirectoryRight;
        if ($targetDirectoryRight === 0) {
            $targetDirectoryRight = self::DEFAULT_RIGHT;
        }

        $this->logger->info('Creating directory: ' . $targetDirectory);
        if (mkdir($targetDirectory, $targetDirectoryRight, true) === false) {
            $this->logger->error('Unable to write directory: ' . $targetDirectory);
            return false;
        }

        return true;
    }
}
