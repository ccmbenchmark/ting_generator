# Ting Generator

Generate entities and repositories for [Ting Datamapper](https://bitbucket.org/ccmbenchmark/ting/overview).

## Launch command

php application.php ting:generate --conf=FULL_PATH_TO_CONFIGURATION_FILE

There's an sample of configuration file in ./sample/conf.php

Target directories will be created at the fly. Make sure you got rights to do it.

## Command option

By default, entities and repositories are generated.

You can generate only entities or only repositories by using the parameter '--mode' :
* 1: only entities
* 2: only repositories
* 3: both