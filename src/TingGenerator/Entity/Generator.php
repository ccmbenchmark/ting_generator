<?php
/***********************************************************************
 *
 * Ting Generator for entity.
 * ==========================================
 *
 * Copyright (C) 2014 CCM Benchmark Group. (http://www.ccmbenchmark.com)
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

namespace CCMBenchmark\TingGenerator\Entity;

use CCMBenchmark\TingGenerator\Infrastructure\PHPType;
use CCMBenchmark\TingGenerator\Log\Logger;
use CCMBenchmark\TingGenerator\Infrastructure\StringFormatter;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlock\Tag\GenericTag;
use Zend\Code\Generator\DocBlock\Tag\ParamTag;
use Zend\Code\Generator\DocBlock\Tag\ReturnTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\Exception\InvalidArgumentException;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;

class Generator
{
    /**
     * @var ClassGenerator
     */
    private $classGenerator;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var StringFormatter
     */
    private $stringFormatter;

    /**
     * Generator constructor.
     * @param ClassGenerator $classGenerator
     * @param Logger $logger
     * @param StringFormatter $stringFormatter
     */
    public function __construct(ClassGenerator $classGenerator, Logger $logger, StringFormatter $stringFormatter)
    {
        $this->classGenerator = $classGenerator;
        $this->logger = $logger;
        $this->stringFormatter = $stringFormatter;
    }

    /**
     * @return ClassGenerator
     */
    public function getClassGenerator()
    {
        return $this->classGenerator;
    }

    /**
     * @param string $entityName
     * @param string $namespace
     * @param array $entityDescription
     *
     * @return $this
     */
    public function generateEntityCode($entityName, $namespace, array $entityDescription)
    {
        $this
            ->generateClassHead($entityName, $namespace)
            ->generateClassBody($entityDescription);

        return $this;
    }

    /**
     * @param string $entityName
     * @param string $namespace
     *
     * @return $this
     */
    private function generateClassHead($entityName, $namespace)
    {
        try {
            $this->classGenerator
                ->setName($entityName)
                ->setNamespaceName($namespace)
                ->addUse('CCMBenchmark\\Ting\\Entity\\NotifyPropertyInterface')
                ->addUse('CCMBenchmark\\Ting\\Entity\\NotifyProperty')
                ->setImplementedInterfaces(['CCMBenchmark\\Ting\\Entity\\NotifyPropertyInterface'])
                ->addTrait('NotifyProperty');
        } catch (InvalidArgumentException $exception) {
            $this->logger->error($exception->getMessage());
        }

        return $this;
    }

    /**
     * @param array $entityDescription
     *
     * @return $this
     */
    private function generateClassBody(array $entityDescription)
    {
        foreach ($entityDescription as $field) {
            $this
                ->addPropertyToClass($field)
                ->addSetterForProperty($field)
                ->addGetterForProperty($field)
            ;
        }

        return $this;
    }

    /**
     * @param PropertyData $propertyData
     *
     * @return $this
     */
    private function addPropertyToClass(PropertyData $propertyData)
    {
        $propertyType = $propertyData->getType();
        $propertyTypeForDocBlock = $propertyType;
        if ($propertyType !== '') {
            $propertyTypeForDocBlock .= '|null';
        }

        try {
            $this->classGenerator
                ->addPropertyFromGenerator(
                    PropertyGenerator::fromArray([
                        'name' => lcfirst($propertyData->getName()),
                        'defaultValue' => null, // @todo: handle default value for property
                        'visibility' => PropertyGenerator::VISIBILITY_PRIVATE,
                        'docblock' => DocBlockGenerator::fromArray([
                            'tags' => [new GenericTag('var', $propertyTypeForDocBlock)]
                        ])
                    ])
                );
        } catch (InvalidArgumentException $exception) {
            $this->logger->error($exception->getMessage());
            return $this;
        }

        return $this;
    }

    /**
     * @param PropertyData $propertyData
     *
     * @return $this
     */
    private function addSetterForProperty(PropertyData $propertyData)
    {
        $setterBody = '';
        $propertyName = $propertyData->getName();
        $varName = '$' . $propertyName;
        $propertyType = $propertyData->getType();

        if ($this->shouldCloneProperty($propertyType) === true) {
            $setterBody .= '$clone = clone ' . $varName . ';' . "\n";
            $varName = '$clone';
        }

        if ($this->shouldCastProperty($propertyType) === true) {
            $setterBody .= $varName . ' = (' . $propertyType . ') ' . $varName . ';' . "\n";
        }

        $setterBody .= '$this->propertyChanged(\'' . $propertyName . '\', $this->' . $propertyName . ', ' . $varName . ');'
            . "\n"
            . '$this->' . $propertyName . ' = ' . $varName . ';'
            . "\n"
            . "\n"
            . 'return $this;';

        try {
            $this->classGenerator
                ->addMethodFromGenerator(
                    MethodGenerator::fromArray([
                        'name'       => 'set' . $propertyName,
                        'parameters' => lcfirst($propertyName),
                        'visibility' => MethodGenerator::VISIBILITY_PUBLIC,
                        'body'       => $setterBody,
                        'docblock'   => DocBlockGenerator::fromArray([
                            'tags' => [
                                new ParamTag($propertyName, [$propertyType]),
                                new ReturnTag(['$this'])
                            ]
                        ])
                    ])
                );
        } catch (InvalidArgumentException $exception) {
            $this->logger->error($exception->getMessage());
            return $this;
        }

        return $this;
    }

    /**
     * @param PropertyData $propertyData
     *
     * @return $this
     */
    private function addGetterForProperty(PropertyData $propertyData)
    {
        $propertyType = $propertyData->getType();
        $propertyName = $propertyData->getName();
        $getterBody = 'return';
        if ($this->shouldCastProperty($propertyType) === true) {
            $getterBody .= ' (' . $propertyType . ')';
        }
        $getterBody .= ' $this->' . lcfirst($propertyName) . ';';

        try {
        $this->classGenerator
            ->addMethodFromGenerator(
                MethodGenerator::fromArray([
                    'name' => 'get',
                    'visibility' => MethodGenerator::VISIBILITY_PUBLIC,
                    'body' => $getterBody,
                    'docblock' => DocBlockGenerator::fromArray([
                        'tags' => new ReturnTag([$propertyType])
                    ])
                ])
            );
        } catch (InvalidArgumentException $exception) {
            $this->logger->error($exception->getMessage());
            return $this;
        }

        return $this;
    }

    /**
     * @param string $propertyType
     *
     * @return bool
     */
    private function shouldCloneProperty($propertyType)
    {
        return $propertyType === PHPType::TYPE_DATETIME;
    }

    /**
     * @param string $propertyType
     *
     * @return bool
     */
    private function shouldCastProperty($propertyType)
    {
        return $propertyType === PHPType::TYPE_STRING || $propertyType === PHPType::TYPE_INT;
    }
}
