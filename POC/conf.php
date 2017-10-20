<?php

$string = new \Infrastructure\StringFormatter();

$classNameFormatter = function ($tableName) use ($string) {
    return $string->camelize($tableName);
};

return [
    [
        /**
         * Database
         */
        'type' => 'mysql',
        'host' => '127.0.0.1',
        'port' => '3302',
        'dbname' => 'auth_ccm_net',
        'charset' => 'utf8',
        'username' => 'mysql',
        'password' => 'p455w0rd',

        /**
         * Return true if the specified table should be excluded.
         */
        'excludeTableFilter' => function ($tableName) {
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
        'entitiesDirectory'       => '/var/www/tinggenerator/AppBundle/Domain/Entity',
        'repositoriesDirectory'   => '/var/www/tinggenerator/AppBundle/Infrastructure/Repository',

        /**
         * Namespaces
         */
        'entityNamespace'         => 'AppBundle\Domain\Entity',
        'repositoryNamespace'     => 'AppBundle\Infrastructure\Repository'
    ]
];