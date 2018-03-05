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
    'port' => '3306',
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

        if ($tableName !== 'test_jvo') {
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
    'entityDirectory'       => '/var/www/ting_generator/generated/AppBundle/Domain/Entity',
    'repositoryDirectory'   => '/var/www/ting_generator/generated/AppBundle/Infrastructure/Repository',

    /**
     * Namespaces
     */
    'entityNamespace'         => 'App\Domain\User\Entity',
    'repositoryNamespace'     => 'App\Infrastructure\Repository\User',

    /**
     * Target directory right
     */
    'targetDirectoryRight' => 0777
];
