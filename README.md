# Ting Generator

Generate entities and repositories for [Ting Datamapper](https://bitbucket.org/ccmbenchmark/ting/overview).

Ting Generator read your SQL tables and create entities and/or repositories automatically.
Update operations on entities and repositories are not yet supported.

## Launch command

php application.php ting:generate --conf=FULL_PATH_TO_CONFIGURATION_FILE -vv

sample: php ./bin/ting-generator ting:generate --conf=/var/www/ting_generator/sample/conf.php --mode=3 -vv

There's an sample of configuration file in ./sample/conf.php

Target directories will be created at the fly. Make sure you got rights to do it.

## Command option

By default, entities and repositories are generated.

You can generate only entities or only repositories by using the parameter '--mode' :
* 1: only entities
* 2: only repositories
* 3: both

## Configuration

You can configure many things. So many possibilities.
Sections with star * are mandatory.

Configuration is a php array returned by a php file. Basically looking like that :

```
<?php

return [
    // Define parameters here...
];
```

### Configure access to your database*

MySQL is the only DBMS supported at the moment. You have to configure theses options :
* host
* port
* databaseName
* charset
* username
* password

Sample :

```
    'type' => 'mysql',
    'host' => '127.0.0.1',
    'port' => '3306',
    'databaseName' => 'auth_ccm_net',
    'charset' => 'utf8',
    'username' => 'mysql',
    'password' => 'p455w0rd',
```

### Exclude tables from generation

Here you have the possibility to set a filter for excluding any tables you want by her name.

Just define a parameter 'excludedTablesFilter' with a closure which will be called for each table read from database. The closure has one argument for the current table's name.

Sample :

You want to exclude table 'DATABASECHANGELOGLOCK'.

```
'excludedTablesFilter' => function ($tableName) {
    if ($tableName === 'DATABASECHANGELOGLOCK') {
        return true;
    }
    
    return false;
}
```

### Format the name of your entities and repositories

By default, Ting Generator take the name of each table and use it to generate names of each corresponding entity and repository.

If you want to format their names differently (camelcase, snakecase etc) you can do it here.

Add parameter 'entityNameFormatter' for entities, 'repositoryNameFormatter' for repositories and define a closure with one parameter for the table name. Your closure just has to return the wanted name.

Sample :

You have a table 'user_role' and want entity&repository with 'UserRole'.
```
'entityNameFormatter' => function ($tableName) {
     return (string) str_replace('_', '', ucwords($tableName, '_'));
 },
 'repositoryNameFormatter' => function ($tableName) {
   return (string) str_replace('_', '', ucwords($tableName, '_'));
};
 ```
 
Of course, you can factorize:

```
$classNameFormatter = function ($tableName) {
    return (string) str_replace('_', '', ucwords($tableName, '_'));
};

return [
    // ...
    'entityNameFormatter'     => $classNameFormatter,
    'repositoryNameFormatter' => $classNameFormatter,
    //...
];
```

### Define namespace for entities & repositories

For entities:

```
'entityNamespace' => 'Beautifull\And\Meaningfull\Namespace\For\Entity'
```

For repositories:
```
'repositoryNamespace' => 'Beautifull\And\Meaningfull\Namespace\For\Repository'
```

### Write generated files where you want

For entities:

```
'entityDirectory' => '/var/www/ting_generator/generated/AppBundle/Domain/Entity',
```

For repositories:

```
'repositoryDirectory'   => '/var/www/ting_generator/generated/AppBundle/Infrastructure/Repository',
```

If you want to set the chmod for directories :

```
'targetDirectoryRight' => 0777
```