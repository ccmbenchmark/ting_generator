<?php

$classNameFormatter = function ($tableName) {
    return (string) str_replace('_', '', ucwords($tableName, '_'));
};

return [
    /**
     * Database
     */
    'type' => 'mysql',
    'host' => '127.0.0.1',
    'port' => '3302',
    'databaseName' => 'auth_ccm_net',
    'charset' => 'utf8',
    'username' => 'mysql',
    'password' => 'p455w0rd',

    /**
     * Return true if the specified table should be excluded.
     */
    'excludedTablesFilter' => function ($tableName) {
        if ($tableName === 'DATABASECHANGELOG' || $tableName === 'DATABASECHANGELOGLOCK') {
            return true;
        }

        return false;
    },
    /**
     * Format name of generated entity from table's name.
     * ucfirst by default. Overrode if you define 'entityNameFormatter'
     */
    'entityNameFormatter'     => $classNameFormatter,
    'repositoryNameFormatter' => $classNameFormatter,

    /**
     * Where files will be written
     */
    'entitiesDirectory'       => '/var/www/ting_generator/generated/AppBundle/Domain/Entity',
    'repositoriesDirectory'   => '/var/www/ting_generator/generated/AppBundle/Infrastructure/Repository',

    /**
     * Namespaces
     */
    'entityNamespace'         => 'AppBundle\Domain\Entity',
    'repositoryNamespace'     => 'AppBundle\Infrastructure\Repository'
];