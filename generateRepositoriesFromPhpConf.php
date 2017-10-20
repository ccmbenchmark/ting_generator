<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/autoload.php';

use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\DocBlock\Tag\ParamTag;
use Zend\Code\Generator\DocBlock\Tag\ReturnTag;
use Zend\Code\Generator\DocBlock\Tag\GenericTag;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\DocBlock\Tag\ThrowsTag;
use Infrastructure\FieldTypeMapping;
use Infrastructure\Logger;
use Infrastructure\StringFormatter;

$logger = new Logger();
$fieldTypeMapping = new FieldTypeMapping();
$string = new StringFormatter();

$logger->info('Loading configuration');

$conf = require __DIR__ . '/conf.php';

foreach ($conf as $dataSource) {
    $logger->info('Connecting to data source');
    $pdo = new PDO(
        sprintf(
            '%s:host=%s;port:%s;dbname=%s;charset=%s',
            $dataSource['type'],
            $dataSource['host'],
            $dataSource['port'],
            $dataSource['dbname'],
            $dataSource['charset']
        ),
        $dataSource['username'],
        $dataSource['password']
    );

    $query = $pdo->query(sprintf('SHOW TABLES FROM %s', $dataSource['dbname']));
    foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $row) {
        /**
         * Handle exclude filter
         */
        if (isset($dataSource['excludeTableFilter']) === true
            && is_callable($dataSource['excludeTableFilter']) === true) {
            if ($dataSource['excludeTableFilter']($row['Tables_in_auth_ccm_net']) === true) {
                continue;
            }
        }

        $query2 = $pdo->query(
            sprintf('DESCRIBE `%s`.`%s`', $dataSource['dbname'], $row['Tables_in_auth_ccm_net'])
        );
        $tableDescription = $query2->fetchAll(PDO::FETCH_ASSOC);

        if (isset($dataSource['repositoryNameFormatter']) === true
            && is_callable($dataSource['repositoryNameFormatter']) === true) {
            $className = $dataSource['repositoryNameFormatter']($row['Tables_in_auth_ccm_net']);
        } else {
            $className = ucfirst($row['Tables_in_auth_ccm_net']);
        }

        $repositoryFullQualifiedName = $className;
        if (isset($dataSource['repositoryNamespace']) === true) {
            $repositoryFullQualifiedName = $dataSource['repositoryNamespace'] . '\\' . $className;
        }

        if (isset($dataSource['entityNameFormatter']) === true
            && is_callable($dataSource['entityNameFormatter']) === true) {
            $entityName = $dataSource['entityNameFormatter']($row['Tables_in_auth_ccm_net']);
        } else {
            $entityName = ucfirst($row['Tables_in_auth_ccm_net']);
        }

        $entityFullQualifiedName = $entityName;
        if (isset($dataSource['entityNamespace']) === true) {
            $entityFullQualifiedName = $dataSource['entityNamespace'] . '\\' . $entityName;
        }

        $logger->info('Writing class');
        $classGenerator = new ClassGenerator();
        $classGenerator
            ->setName($repositoryFullQualifiedName)
            ->addUse('CCMBenchmark\\Ting\\Exception')
            ->addUse('AppBundle\\Domain\\Client\\Entity')
            ->addUse('CCMBenchmark\\Ting\\Repository\\Metadata')
            ->addUse('CCMBenchmark\\Ting\\Repository\\MetadataInitializer')
            ->addUse('CCMBenchmark\\Ting\\Repository\\Repository')
            ->addUse('CCMBenchmark\\Ting\\Serializer\\SerializerFactoryInterface')
            ->setExtendedClass('Repository')
            ->setImplementedInterfaces(['MetadataInitializer']);

        $body =
            '$metadata = new Metadata($serializerFactory);'
            . "\n" . '$metadata->setEntity(' . $entityFullQualifiedName . '::class);'
            . "\n" . '$metadata->setConnectionName($options[\'connection\']);'
            . "\n" . '$metadata->setDatabase($options[\'database\']);'
            . "\n" . '$metadata->setTable(\'' . $row['Tables_in_auth_ccm_net'] . '\');'
            . "\n\n"
            . '$metadata';
        foreach ($tableDescription as $field) {
            $body .=
                "\n" . '    ->addField(['
                . "\n" . '        \'fieldName\' => \'' . $field['Field'] . '\','
                . "\n" . '        \'columnName\' => \'' . lcfirst($string->camelize($field['Field'])) . '\','
                . "\n" . '        \'type\' => \'' . $fieldTypeMapping->getFromMysqlType($field['Type']) . '\'';

            if ($field['Key'] === 'PRI') {
                $body .= ',' . "\n" . '        \'primary\' => true';
            }

            $body .= "\n" . '    ])';
        }
        $body .= ';' . "\n\n" . 'return $metadata;';

        $classGenerator
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
                'body' => $body
            ]));

        $logger->info('Class generated !');

        $fileGenerator = new FileGenerator();
        $fileGenerator->setClass($classGenerator);
        $directory = $dataSource['repositoriesDirectory'];
        if (is_dir($directory) === false) {
            mkdir($directory, 0777, true);
        }

        $filename = $directory . '/' . $entityName . '.php';

        $logger->info('Writing file ' . $filename);

        file_put_contents($filename, $fileGenerator->generate());
    }
}

$logger->info('Done');
