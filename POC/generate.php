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
$conf = simplexml_load_file('./conf.xml');

foreach ($conf->data_source as $dataSource) {

    $dataSourceAttributes = $dataSource->attributes();

    $logger->info('Connecting to data source');
    $pdo = new PDO(
        sprintf(
            '%s:host=%s;port:%s;dbname=%s;charset=%s',
            $dataSourceAttributes->type,
            $dataSourceAttributes->host,
            $dataSourceAttributes->port,
            $dataSourceAttributes->dbname,
            $dataSourceAttributes->charset
        ),
        $dataSourceAttributes->username,
        $dataSourceAttributes->password
    );

    foreach ($dataSource->generate as $generate) {

        $generateAttributes = $generate->attributes();

        $query = $pdo->query(sprintf(
            'DESCRIBE `%s`.`%s`',
            $dataSourceAttributes->dbname,
            $generateAttributes->from_table
         ));
        $tableDescription = $query->fetchAll(PDO::FETCH_ASSOC);

        $logger->info('Writing class');
        $classGenerator = new ClassGenerator();
        $classGenerator
            ->setName($generateAttributes->with_namespace . '\\' . $generateAttributes->entity)
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
            if ($fieldType === FieldTypeMapping::TYPE_STRING || $fieldType === FieldTypeMapping::TYPE_STRING) {
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

        $directory = __DIR__ . 'generate.php/' . str_replace('\\', '/', $generateAttributes->with_namespace);
        if (is_dir($directory) === false) {
            mkdir($directory, 0777, true);
        }

        $filename = $directory . '/' . $generateAttributes->entity . '.php';

        $logger->info('Writing file ' . $filename);

        file_put_contents($filename, $fileGenerator->generate());
    }
}

$logger->info('Done');
