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
$string = new StringFormatter();

$logger->info('Connecting to database');
$pdo = new PDO(
        'mysql:host=127.0.0.1;port:3306;dbname=auth_ccm_net;charset=utf8',
        'mysql',
        'p455w0rd'
);

$logger->info('Reading table info');
$query = $pdo->query('DESCRIBE auth_ccm_net.user');

$fieldTypeMapping = new FieldTypeMapping();

$logger->info('Writing class');
$classGenerator = new ClassGenerator();
$classGenerator
    ->setName('User')
    ->setName('AppBundle\\Domain\\User\\Entity\\User')
    ->addUse('CCMBenchmark\\Ting\\Entity\\NotifyPropertyInterface')
    ->addUse('CCMBenchmark\\Ting\\Entity\\NotifyProperty')
    ->setImplementedInterfaces(['CCMBenchmark\\Ting\\Entity\\NotifyPropertyInterface'])
    ->addTrait('NotifyProperty');

foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $field) {

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
                        'tags'     => [
                        new GenericTag('var', $fieldTypeString)
                    ]
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

    if ($fieldType === FieldTypeMapping::TYPE_STRING || $fieldType === FieldTypeMapping::TYPE_STRING) {
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
                    'tags' => [new ReturnTag([$fieldType])]
                ])
            ])
        );
}

$logger->info('Class generated !');

$fileGenerator = new FileGenerator();
$fileGenerator->setClass($classGenerator);

$logger->info('Writing file');
file_put_contents('./User.php', $fileGenerator->generate());
$logger->info('Done.');
