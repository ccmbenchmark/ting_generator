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

        if (isset($dataSource['entityNameFormatter']) === true
            && is_callable($dataSource['entityNameFormatter']) === true) {
            $entityName = $dataSource['entityNameFormatter']($row['Tables_in_auth_ccm_net']);
        } else {
            $entityName = ucfirst($row['Tables_in_auth_ccm_net']);
        }

        $logger->info('Writing class');
        $classGenerator = new ClassGenerator();
        $classGenerator
            ->setName($entityName)
            ->setNamespaceName($dataSource['entityNamespace'])
            ->addUse('CCMBenchmark\\Ting\\Entity\\NotifyPropertyInterface')
            ->addUse('CCMBenchmark\\Ting\\Entity\\NotifyProperty')
            ->setImplementedInterfaces(['CCMBenchmark\\Ting\\Entity\\NotifyPropertyInterface'])
            ->addTrait('NotifyProperty');

        foreach ($tableDescription as $field) {
            $fieldType = $fieldTypeMapping->getFromMysqlType($field['Type']);
            $fieldTypeString = $fieldType;
            if ($fieldType !== null) {
                $fieldTypeString .= '|null';
            }

            $propertyName = lcfirst($string->camelize($field['Field']));
            $propertyNameForMethod = $string->camelize($field['Field']);

            $logger->info('Writing property: \'' . $propertyName . '\'');
            $classGenerator
                ->addPropertyFromGenerator(
                    PropertyGenerator::fromArray([
                     'name'         => $propertyName,
                     'defaultValue' => null, //@todo: handle custom default value
                     'visibility'   => PropertyGenerator::VISIBILITY_PRIVATE,
                     'docblock'     => DocBlockGenerator::fromArray([
                            'tags'         => [new GenericTag('var', $fieldTypeString)]
                        ])
                    ])
                );

            $logger->info('Writing setter for property: \'' . $propertyName . '\'');
            $body = '';
            $varName = '$' . $propertyName;

            if ($fieldType === FieldTypeMapping::TYPE_DATETIME) {
                $body .= '$clone = clone ' . $varName . ';' . "\n";
                $varName = '$clone';
            }

            if ($fieldType === FieldTypeMapping::TYPE_STRING || $fieldType === FieldTypeMapping::TYPE_INT) {
                $body .= $varName . ' = (' . $fieldType . ') ' . $varName . ';' . "\n";
            }

            $body .= '$this->propertyChanged(\'' . $propertyName . '\', $this->' . $propertyName . ', ' . $varName . ');'
                     . "\n"
                     . '$this->' . $propertyName . ' = ' . $varName . ';'
                     . "\n"
                     . "\n"
                     . 'return $this;';

            $classGenerator
                ->addMethodFromGenerator(
                    MethodGenerator::fromArray([
                       'name'       => 'set' . $propertyNameForMethod,
                       'parameters' => [$propertyName],
                       'visibility' => MethodGenerator::VISIBILITY_PUBLIC,
                       'body'       => $body,
                       'docblock'   => DocBlockGenerator::fromArray([
                            'tags' => [
                                new ParamTag($propertyName, [$fieldType]),
                                new ReturnTag(['$this'])
                            ]
                        ])
                   ])
                );

            $logger->info('Writing getter for property: \'' . $propertyName . '\'');

            $cast = '';
            if ($fieldType === FieldTypeMapping::TYPE_STRING || $fieldType === FieldTypeMapping::TYPE_INT) {
                $cast = ' (' . $fieldType . ')';
            }

            $classGenerator
                ->addMethodFromGenerator(
                    MethodGenerator::fromArray([
                       'name'       => 'get' . $propertyNameForMethod,
                       'visibility' => MethodGenerator::VISIBILITY_PUBLIC,
                       'body'       => 'return' . $cast . ' $this->' . $propertyName . ';',
                       'docblock'   => DocBlockGenerator::fromArray([
                            'tags'       => [new ReturnTag([$fieldType])]
                        ])
                   ])
                );
        }
        $logger->info('Class generated !');

        $fileGenerator = new FileGenerator();
        $fileGenerator->setClass($classGenerator);

        $directory = $dataSource['entitiesDirectory'];
        if (is_dir($directory) === false) {
            mkdir($directory, 0777, true);
        }

        $filename = $directory . '/' . $entityName . '.php';

        $logger->info('Writing file ' . $filename);

        file_put_contents($filename, $fileGenerator->generate());
    }
}


$logger->info('Done');
