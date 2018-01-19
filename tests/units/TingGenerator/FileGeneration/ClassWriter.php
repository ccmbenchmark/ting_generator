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

namespace tests\units\CCMBenchmark\TingGenerator\FileGeneration;

use mageekguy\atoum;
use Psr\Log\LoggerInterface;
use CCMBenchmark\TingGenerator\FileGeneration\FileGeneratorFactory;
use Zend\Code\Generator\FileGenerator;
use CCMBenchmark\TingGenerator\FileGeneration;

/**
 * Class ClassWriter
 * @package tests\units\CCMBenchmark\TingGenerator\FileGeneration
 * @tags ClassWriter
 */
class ClassWriter extends atoum
{
    /**
     * @var FileGenerator
     */
    private $fileGenerator;

    /**
     * @var FileGeneratorFactory
     */
    private $fileGeneratorFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param string $testMethod
     */
    public function beforeTestMethod($testMethod)
    {
        $this->fileGenerator = new \mock\Zend\Code\Generator\FileGenerator();
        $this->fileGeneratorFactory = new \mock\CCMBenchmark\TingGenerator\FileGeneration\FileGeneratorFactory();
        $this->calling($this->fileGeneratorFactory)->get = $this->fileGenerator;

        $this->logger = new \mock\Psr\Log\LoggerInterface();
    }
    
    public function testWriteCanWriteFileWithDefaultRight()
    {
        $this
            ->given($className = uniqid('className'))
            ->and($classGenerator = new \mock\Zend\Code\Generator\ClassGenerator())
            ->and($targetDirectory = uniqid('targetDirectory'))
            ->and($this->function->is_dir = false)
            ->and($this->function->mkdir = true)
            ->and($this->calling($this->fileGenerator)->write = $this->fileGenerator)
            ->and($this->newTestedInstance($this->fileGeneratorFactory, $this->logger))
                ->boolean(
                    $this->testedInstance->write($className, $classGenerator, $targetDirectory)
                )
                    ->isTrue()
            ->mock($this->fileGenerator)
                ->call('setFilename')
                    ->withIdenticalArguments($targetDirectory . '/' . $className . '.php')
                        ->once()
                ->call('setClass')
                    ->withIdenticalArguments($classGenerator)
                        ->once()
                ->call('write')
                    ->once()
            ->function('mkdir')
                ->wasCalledWithIdenticalArguments(
                    $targetDirectory,
                    FileGeneration\ClassWriter::DEFAULT_RIGHT,
                    true
                )
                    ->once()
        ;
    }
}
