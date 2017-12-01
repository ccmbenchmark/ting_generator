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

namespace CCMBenchmark\TingGenerator\Generator;

use CCMBenchmark\TingGenerator\Database\TableDescription;
use Zend\Code\Generator\ClassGenerator;
use CCMBenchmark\TingGenerator\Log\Logger;
use CCMBenchmark\TingGenerator\Infrastructure\StringFormatter;
use Zend\Code\Generator\DocBlock\Tag\ParamTag;
use Zend\Code\Generator\DocBlock\Tag\ReturnTag;
use Zend\Code\Generator\DocBlock\Tag\ThrowsTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use CCMBenchmark\TingGenerator\Database\FieldDescription;

class Repository
{
    /**
     * @var ClassGenerator
     */
    private $baseClassGenerator;

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
        $this->baseClassGenerator = $classGenerator;
        $this->logger = $logger;
        $this->stringFormatter = $stringFormatter;
        $this->initializeClassGenerator();
    }

    /**
     * Class generator must be cleaned before each generation.
     * Else it gonna keep in memory the previous generated class.
     *
     * @return $this
     */
    private function initializeClassGenerator()
    {
        $this->classGenerator = clone $this->baseClassGenerator;

        return $this;
    }

    /**
     * @return ClassGenerator
     */
    public function getClassGenerator()
    {
        return $this->classGenerator;
    }

    /**
     * @param string $repositoryName
     * @param string $repositoryNamespace
     * @param TableDescription $tableDescription
     * @param string $entityFullQualifiedName
     *
     * @throws \Zend\Code\Generator\Exception\InvalidArgumentException
     *
     * @return $this;
     */
    public function generateRepositoryCode(
        $repositoryName,
        $repositoryNamespace,
        TableDescription $tableDescription,
        $entityFullQualifiedName
    ) {
        $this
            ->initializeClassGenerator()
            ->generateClassHead($repositoryName, $repositoryNamespace)
            ->generateClassBody($tableDescription, $entityFullQualifiedName);

        return $this;
    }

    /**
     * @param string $repositoryName
     * @param string $repositoryNamespace
     *
     * @return $this
     */
    private function generateClassHead($repositoryName, $repositoryNamespace)
    {
        $this->classGenerator
            ->setName((string) $repositoryName)
            ->setNamespaceName((string) $repositoryNamespace)
            ->addUse('CCMBenchmark\\Ting\\Exception')
            ->addUse('AppBundle\\Domain\\Client\\Entity')
            ->addUse('CCMBenchmark\\Ting\\Repository\\Metadata')
            ->addUse('CCMBenchmark\\Ting\\Repository\\MetadataInitializer')
            ->addUse('CCMBenchmark\\Ting\\Repository\\Repository')
            ->addUse('CCMBenchmark\\Ting\\Serializer\\SerializerFactoryInterface')
            ->setExtendedClass('CCMBenchmark\\Ting\\Repository\\Repository')
            ->setImplementedInterfaces(['MetadataInitializer']);

        return $this;
    }

    /**
     * @param TableDescription $tableDescription
     * @param string $entityFullQualifiedName
     *
     * @throws \Zend\Code\Generator\Exception\InvalidArgumentException
     *
     * @return $this
     */
    private function generateClassBody(TableDescription $tableDescription, $entityFullQualifiedName)
    {
        $this->classGenerator
            ->addMethodFromGenerator(MethodGenerator::fromArray([
                'name' => 'initMetadata',
                'static' => true,
                'visibility' => 'public',
                'parameters' => [
                    ParameterGenerator::fromArray([
                        'name' => 'serializerFactory',
                        'type' => 'SerializerFactoryInterface'
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
                'body' => $this->getBodyForInitMetadataFunction($tableDescription, $entityFullQualifiedName)
            ]));

        return $this;
    }

    /**
     * @param TableDescription $tableDescription
     * @param string $entityFullQualifiedName
     * @return string
     */
    private function getBodyForInitMetadataFunction(TableDescription $tableDescription, $entityFullQualifiedName)
    {
        $body =
            '$metadata = new Metadata($serializerFactory);'
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
            $body .= $this->getFieldDeclarationForInitMetadataFunction($fieldDescription);
        }
        $body .= ';' . "\n\n" . 'return $metadata;';

        return $body;
    }

    /**
     * @param FieldDescription $fieldDescription
     * @return string
     */
    private function getFieldDeclarationForInitMetadataFunction(FieldDescription $fieldDescription)
    {
        $fieldName = $fieldDescription->getName();
        $body = "\n" . '    ->addField([';

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

        return $body;
    }
}
