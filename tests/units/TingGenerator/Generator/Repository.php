<?php
/***********************************************************************
 *
 * Ting Generator for entity.
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

namespace tests\units\CCMBenchmark\TingGenerator\Generator;

use CCMBenchmark\Ting\Exception;
use CCMBenchmark\Ting\Repository\Metadata;
use CCMBenchmark\Ting\Repository\MetadataInitializer;
use CCMBenchmark\Ting\Serializer\SerializerFactoryInterface;
use CCMBenchmark\TingGenerator\Database\FieldDescription;
use CCMBenchmark\TingGenerator\Database\TableDescription;
use CCMBenchmark\TingGenerator\Infrastructure\StringFormatter;
use Psr\Log\LoggerInterface;
use Zend\Code\Generator\ClassGenerator;
use CCMBenchmark\Ting\Repository\Repository as TingRepository;
use Zend\Code\Generator\DocBlock\Tag\ParamTag;
use Zend\Code\Generator\DocBlock\Tag\ReturnTag;
use Zend\Code\Generator\DocBlock\Tag\ThrowsTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;

/**
 * Class Repository
 * @package tests\units\CCMBenchmark\TingGenerator\Generator
 * @tags RepositoryGenerator
 */
class Repository extends \atoum
{
    /**
     * @var \CCMBenchmark\TingGenerator\Generator\ClassGeneratorFactory
     */
    private $classGeneratorFactory;

    /**
     * @var ClassGenerator
     */
    private $classGenerator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StringFormatter
     */
    private $stringFormatter;

    /**
     * @param string $testMethod
     */
    public function beforeTestMethod($testMethod)
    {
        $this->classGenerator = new \mock\Zend\Code\Generator\ClassGenerator();
        $this->classGeneratorFactory = new \mock\CCMBenchmark\TingGenerator\Generator\ClassGeneratorFactory();
        $this->logger = new \mock\Psr\Log\LoggerInterface();
        $this->stringFormatter = new \mock\CCMBenchmark\TingGenerator\Infrastructure\StringFormatter();

        $this->calling($this->classGeneratorFactory)->get = $this->classGenerator;
    }

    public function testGetClassGeneratorReturnClassGenerator()
    {
        $this
            ->given($this->newTestedInstance($this->classGeneratorFactory, $this->logger, $this->stringFormatter))
                ->object($this->testedInstance->getClassGenerator())
                    ->isInstanceOf(ClassGenerator::class);
    }

    /**
     * @return array
     */
    private function getRandomTableDescription()
    {
        $entityDescription = [];
        $entityDescription[] = new FieldDescription(
            uniqid('type'),
            uniqid('name'),
            true,
            true
        );
        $entityDescription[] = new FieldDescription(
            uniqid('type'),
            uniqid('name'),
            false,
            false
        );

        return $entityDescription;
    }

    public function testGenerateRepositoryCode()
    {
        $this
            ->given($repositoryName = uniqid('repositoryName'))
            ->and($repositoryNamespace = uniqid('repositoryNamespace'))
            ->and(
                $tableDescription = new TableDescription(
                    uniqid('name'),
                    $this->getRandomTableDescription()
                )
            )
            ->and($entityFullQualifiedName = uniqid('entityFullQualifiedName'))
            ->and($this->calling($this->stringFormatter)->formatEntityNamespace = $entityFullQualifiedName)
            ->and(
                $this->newTestedInstance($this->classGeneratorFactory, $this->logger, $this->stringFormatter)
            )
            ->object(
                $this->testedInstance->generateRepositoryCode(
                    $repositoryName,
                    $repositoryNamespace,
                    $tableDescription,
                    $entityFullQualifiedName
                )
            )
            ->isTestedInstance()
            ->mock($this->classGenerator)
                ->call('setName')
                    ->withIdenticalArguments($repositoryName)
                        ->once()
                ->call('setNamespaceName')
                    ->withIdenticalArguments($repositoryNamespace)
                        ->once()
                ->call('addUse')
                    ->withIdenticalArguments(Exception::class)
                        ->once()
                ->call('addUse')
                    ->withIdenticalArguments(Metadata::class)
                        ->once()
                ->call('addUse')
                    ->withIdenticalArguments(MetadataInitializer::class)
                        ->once()
                ->call('addUse')
                    ->withIdenticalArguments(TingRepository::class)
                        ->once()
                ->call('addUse')
                    ->withIdenticalArguments(SerializerFactoryInterface::class)
                        ->once()
                ->call('setExtendedClass')
                    ->withIdenticalArguments(TingRepository::class)
                        ->once()
                ->call('setImplementedInterfaces')
                    ->withIdenticalArguments([MetadataInitializer::class])
                        ->once()
        ;

        $body = '$metadata = new Metadata($serializerFactory);'
            . "\n" . '$metadata->setEntity(' . $entityFullQualifiedName . '::class);'
            . "\n" . '$metadata->setConnectionName($options[\'connection\']);'
            . "\n" . '$metadata->setDatabase($options[\'database\']);'
            . "\n" . '$metadata->setTable(\'' . $tableDescription->getName() . '\');'
            . "\n\n"
            . '$metadata';
        /**
         * @var FieldDescription $fieldDescription
         */
        foreach ($tableDescription->getFieldsDescription() as $fieldDescription) {
            $fieldName = $fieldDescription->getName();
            $body .= "\n" . '    ->addField([';

            if ($fieldDescription->isPrimary() === true) {
                $body .= "\n" . '        \'primary\' => true,';
            }

            if ($fieldDescription->isAutoIncrement() === true) {
                $body .= "\n" . '        \'autoincrement\' => true,';
            }

            $body .=
                "\n" . '        \'fieldName\' => \'' . $fieldName . '\','
                . "\n" . '        \'columnName\' => \'' . lcfirst($this->stringFormatter->camelize($fieldName)) . '\','
                . "\n" . '        \'type\' => \'' . $fieldDescription->getType() . '\'';

            $body .= "\n" . '    ])';
        }

        $body .= ';' . "\n\n" . 'return $metadata;';

        try {
            $this
                ->mock($this->classGenerator)
                    ->call('addMethodFromGenerator')
                        ->withArguments(MethodGenerator::fromArray([
                            'name' => 'initMetadata',
                            'static' => true,
                            'visibility' => 'public',
                            'parameters' => [
                                ParameterGenerator::fromArray([
                                    'name' => 'serializerFactory',
                                    'type' => SerializerFactoryInterface::class
                                ]),
                                ParameterGenerator::fromArray([
                                    'name' => 'options',
                                    'defaultvalue' => null,
                                    'type' => 'array'
                                ])
                            ],
                            'docblock' => DocBlockGenerator::fromArray([
                                'shortdescription' => 'Initialize metadata.',
                                'tags' => [
                                    new ParamTag('serializerFactory', ['SerializerFactoryInterface']),
                                    new ParamTag('options', ['array']),
                                    new ReturnTag(['Metadata']),
                                    new ThrowsTag(['Exception'])
                                ]
                            ]),
                            'body' => $body
                        ]))
                            ->once();
        } catch (\InvalidArgumentException $exception) {
            echo $exception->getMessage();
        }
    }
}
