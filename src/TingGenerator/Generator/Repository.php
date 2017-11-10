<?php

namespace CCMBenchmark\TingGenerator\Generator;

use Zend\Code\Generator\ClassGenerator;
use CCMBenchmark\TingGenerator\Log\Logger;
use CCMBenchmark\TingGenerator\Infrastructure\StringFormatter;
use Zend\Code\Generator\DocBlock\Tag\ParamTag;
use Zend\Code\Generator\DocBlock\Tag\ReturnTag;
use Zend\Code\Generator\DocBlock\Tag\ThrowsTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;

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
     * @param array $repositoryNamespace
     * @param array $tableDescription
     * @param string $entityFullQualifiedName
     *
     * @throws \Zend\Code\Generator\Exception\InvalidArgumentException
     *
     * @return $this;
     */
    public function generateRepositoryCode(
        $repositoryName,
        $repositoryNamespace,
        array $tableDescription,
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
     * @param array $tableDescription
     * @param string $entityFullQualifiedName
     *
     * @throws \Zend\Code\Generator\Exception\InvalidArgumentException
     *
     * @return $this
     */
    private function generateClassBody(array $tableDescription, $entityFullQualifiedName)
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
     * @param array $tableDescription
     * @param string $entityFullQualifiedName
     * @return string
     */
    private function getBodyForInitMetadataFunction(array $tableDescription, $entityFullQualifiedName)
    {
        $body =
            '$metadata = new Metadata($serializerFactory);'
            . "\n" . '$metadata->setEntity(' . $entityFullQualifiedName . '::class);'
            . "\n" . '$metadata->setConnectionName($options[\'connection\']);'
            . "\n" . '$metadata->setDatabase($options[\'database\']);'
            // @todo: handle table name
            //. "\n" . '$metadata->setTable(\'' . $tableDescription['tableName'] . '\');'
            . "\n\n"
            . '$metadata';

        /**
         * @var PropertyData $propertyData
         */
        foreach ($tableDescription as $propertyData) {
            $body .= $this->getFieldDeclarationforInitMetadataFunction($propertyData);
        }
        $body .= ';' . "\n\n" . 'return $metadata;';

        return $body;
    }

    /**
     * @param PropertyData $propertyData
     * @return string
     */
    private function getFieldDeclarationforInitMetadataFunction(PropertyData $propertyData)
    {
        $fieldName = $propertyData->getName();
        $body =
            "\n" . '    ->addField(['
            . "\n" . '        \'fieldName\' => \'' . $fieldName . '\','
            . "\n" . '        \'columnName\' => \'' . lcfirst($this->stringFormatter->camelize($fieldName)) . '\','
            . "\n" . '        \'type\' => \'' . $propertyData->getType() . '\'';

        if ($propertyData->isPrimary() === true) {
            $body .= ',' . "\n" . '        \'primary\' => true';
        }
        $body .= "\n" . '    ])';

        return $body;
    }
}
