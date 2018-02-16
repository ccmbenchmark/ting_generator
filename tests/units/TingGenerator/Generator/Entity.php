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

use CCMBenchmark\Ting\Entity\NotifyProperty;
use CCMBenchmark\Ting\Entity\NotifyPropertyInterface;
use CCMBenchmark\TingGenerator\Database\FieldDescription;
use CCMBenchmark\TingGenerator\Infrastructure\PHPType;
use mageekguy\atoum;
use Zend\Code\Generator\ClassGenerator;
use Psr\Log\LoggerInterface;
use CCMBenchmark\TingGenerator\Infrastructure\StringFormatter;
use Zend\Code\Generator\DocBlock\Tag\GenericTag;
use Zend\Code\Generator\DocBlock\Tag\ParamTag;
use Zend\Code\Generator\DocBlock\Tag\ReturnTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\Exception\InvalidArgumentException;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Class Entity
 * @package tests\units\CCMBenchmark\TingGenerator\Generator
 * @tags EntityGenerator
 */
class Entity extends atoum
{
    /**
     * @var ClassGenerator
     */
    private $classGenerator;

    /**
     * @var \CCMBenchmark\TingGenerator\Generator\ClassGeneratorFactory
     */
    private $classGeneratorFactory;

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
    private function getRandomEntityDescription()
    {
        $entityDescription = [];
        for ($i = 0; $i < rand(2, 10); $i++) {
            $entityDescription[] = new FieldDescription(
                uniqid('type'),
                uniqid('name'),
                rand(0, 1) === 0,
                rand(0, 1) === 0
            );
        }

        return $entityDescription;
    }

    public function testGenerateEntityCode()
    {
        $this
            ->given($entityName = uniqid('entityName'))
            ->and($namespace = uniqid('namespace'))
            ->and(
                $entityDescription = $this->getRandomEntityDescription()
            )
            ->and($this->calling($this->classGeneratorFactory)->get = $this->classGenerator)
            ->and($this->newTestedInstance($this->classGeneratorFactory, $this->logger, $this->stringFormatter))
            ->object($this->testedInstance->generateEntityCode($entityName, $namespace, $entityDescription))
                ->isTestedInstance()
            ->mock($this->classGenerator)
                ->call('setName')
                    ->withIdenticalArguments($entityName)
                        ->once()
                ->call('setNamespaceName')
                    ->withIdenticalArguments($namespace)
                        ->once()
                ->call('addUse')
                    ->withIdenticalArguments(NotifyPropertyInterface::class)
                        ->once()
                ->call('addUse')
                    ->withIdenticalArguments(NotifyProperty::class)
                        ->once()
                ->call('setImplementedInterfaces')
                    ->withIdenticalArguments([NotifyPropertyInterface::class])
                        ->once()
                ->call('addTrait')
                    ->withIdenticalArguments('NotifyProperty')
                        ->once()
        ;

        /**
         * @var FieldDescription $description
         */
        foreach ($entityDescription as $description) {
            try {
                $propertyName = lcfirst($this->stringFormatter->camelize($description->getName()));
                $parameterName = '$' . $propertyName;
                $setterBody =
                    '$this->propertyChanged(\'' . $propertyName . '\', $this->' . $propertyName . ', '
                        . $parameterName . ');'
                    . "\n"
                    . '$this->' . $propertyName . ' = ' . $parameterName . ';'
                    . "\n"
                    . "\n"
                    . 'return $this;';

                $getterBody = 'return' . ' $this->' . $propertyName . ';';

                $this
                    ->mock($this->classGenerator)
                        ->call('addPropertyFromGenerator')
                            ->withArguments(
                                PropertyGenerator::fromArray([
                                    'name' => lcfirst($this->stringFormatter->camelize($description->getName())),
                                    'defaultValue' => null,
                                    'visibility' => PropertyGenerator::VISIBILITY_PRIVATE,
                                    'docblock' => DocBlockGenerator::fromArray([
                                        'tags' => [new GenericTag('var', $description->getType() . '|null')]
                                    ])
                                ])
                            )
                                ->once()
                        ->call('addMethodFromGenerator')
                            ->withArguments(
                                MethodGenerator::fromArray([
                                    'name'       => 'set' . $this->stringFormatter->ucfirst(
                                        $this->stringFormatter->camelize($description->getName())
                                    ),
                                    'parameters' => [$propertyName],
                                    'visibility' => MethodGenerator::VISIBILITY_PUBLIC,
                                    'body'       => $setterBody,
                                    'docblock'   => DocBlockGenerator::fromArray([
                                        'tags' => [
                                            new ParamTag($propertyName, [$description->getType()]),
                                            new ReturnTag(['$this'])
                                        ]
                                    ])
                                ])
                            )
                                ->once()
                        ->call('addMethodFromGenerator')
                            ->withArguments(
                                MethodGenerator::fromArray([
                                    'name' => 'get' . $this->stringFormatter->ucfirst($propertyName),
                                    'visibility' => MethodGenerator::VISIBILITY_PUBLIC,
                                    'body' => $getterBody,
                                    'docblock' => DocBlockGenerator::fromArray([
                                        'tags' => [new ReturnTag([$description->getType()])]
                                    ])
                                ])
                            )
                                ->once()
                ;
            } catch (InvalidArgumentException $exception) {
                echo $exception->getMessage();
            }
        }
    }

    /**
     * @param string $type
     */
    private function generateEntityCodeHandle($type)
    {
        try {
            $this
                ->given($entityName = uniqid('entityName'))
                ->and($namespace = uniqid('namespace'))
                ->and(
                    $entityDescription =
                        new FieldDescription(
                            $type,
                            uniqid('name'),
                            rand(0, 1) === 0,
                            rand(0, 1) === 0
                        )
                )
                ->and($this->calling($this->classGeneratorFactory)->get = $this->classGenerator)
                ->and($propertyName = lcfirst($this->stringFormatter->camelize($entityDescription->getName())))
                ->and($getterBody = 'return (' . $type . ') $this->' . $propertyName . ';')
                ->and($this->newTestedInstance($this->classGeneratorFactory, $this->logger, $this->stringFormatter))
                ->object($this->testedInstance->generateEntityCode($entityName, $namespace, [$entityDescription]))
                    ->isTestedInstance()
                ->mock($this->classGenerator)
                    ->call('addMethodFromGenerator')
                        ->withArguments(
                            MethodGenerator::fromArray([
                                'name' => 'get' . $this->stringFormatter->ucfirst($propertyName),
                                'visibility' => MethodGenerator::VISIBILITY_PUBLIC,
                                'body' => $getterBody,
                                'docblock' => DocBlockGenerator::fromArray([
                                    'tags' => [new ReturnTag([$entityDescription->getType()])]
                                ])
                            ])
                        )
                            ->once()
            ;
        } catch (InvalidArgumentException $exception) {
            echo $exception->getMessage();
        }
    }

    public function testGenerateEntityCodeHandleInteger()
    {
        $this->generateEntityCodeHandle(PHPType::TYPE_INT);
    }

    public function testGenerateEntityCodeHandleString()
    {
        $this->generateEntityCodeHandle(PHPType::TYPE_STRING);
    }

    public function testGenerateEntityClonePropertyInSetter()
    {
        try {
            $this
                ->given($entityName = uniqid('entityName'))
                ->and($namespace = uniqid('namespace'))
                ->and(
                    $entityDescription =
                        new FieldDescription(
                            PHPType::TYPE_DATETIME,
                            uniqid('name'),
                            rand(0, 1) === 0,
                            rand(0, 1) === 0
                        )
                )
                ->and($this->calling($this->classGeneratorFactory)->get = $this->classGenerator)
                ->and($propertyName = lcfirst($this->stringFormatter->camelize($entityDescription->getName())))
                ->and($parameterName = '$clone')
                ->and($setterBody =
                    '$clone = clone $' . $propertyName . ';' . "\n"
                    . '$this->propertyChanged(\'' . $propertyName . '\', $this->' . $propertyName . ', '
                    . $parameterName . ');'
                    . "\n"
                    . '$this->' . $propertyName . ' = ' . $parameterName . ';'
                    . "\n"
                    . "\n"
                    . 'return $this;')
                ->and($this->newTestedInstance($this->classGeneratorFactory, $this->logger, $this->stringFormatter))
                ->object($this->testedInstance->generateEntityCode($entityName, $namespace, [$entityDescription]))
                ->isTestedInstance()
                ->mock($this->classGenerator)
                    ->call('addMethodFromGenerator')
                        ->withArguments(
                            MethodGenerator::fromArray([
                                'name'       => 'set' . $this->stringFormatter->ucfirst(
                                    $this->stringFormatter->camelize($entityDescription->getName())
                                ),
                                'parameters' => [$propertyName],
                                'visibility' => MethodGenerator::VISIBILITY_PUBLIC,
                                'body'       => $setterBody,
                                'docblock'   => DocBlockGenerator::fromArray([
                                    'tags' => [
                                        new ParamTag($propertyName, [$entityDescription->getType()]),
                                        new ReturnTag(['$this'])
                                    ]
                                ])
                            ])
                        )
                            ->once()
            ;
        } catch (InvalidArgumentException $exception) {
            echo $exception->getMessage();
        }
    }
}
