<?php

namespace CCMBenchmark\TingGenerator\Database\MySQL;

class TypeMapping
{
    const TYPE_INT = 'int';
    const TYPE_STRING = 'string';
    const TYPE_DATETIME = '\Datetime';

    /**
     * @param string $mysqlType
     *
     * @return null|string
     */
    public function getFromMysqlType($mysqlType)
    {
        $mysqlType = trim(mb_strtolower($mysqlType));

        if ($this->isInteger($mysqlType) === true) {
            return self::TYPE_INT;
        }

        if ($this->isString($mysqlType) === true || $this->isJson($mysqlType) === true) {
            return self::TYPE_STRING;
        }

        if ($this->isDateTime($mysqlType) === true) {
            return self::TYPE_DATETIME;
        }

        return null;
    }

    /**
     * @param string $mysqlType
     *
     * @return bool Return true if $mysqlType is an integer, false if not.
     */
    private function isInteger($mysqlType)
    {
        return preg_match('~^tinyint|smallint|mediumint|int|bigint|bit|float|double|decimal~', $mysqlType) === 1;
    }

    /**
     * @param string $mysqlType
     *
     * @return bool Return true if $mysqlType is a string, false if not.
     */
    private function isString($mysqlType)
    {
        return preg_match(
                '~^char|varchar|tinytext|text|mediumtext|longtext|json'.
                '|binary|varbinary|tinyblob|blob|mediumblob|longblob|enum|set~',
                $mysqlType
            ) === 1;
    }

    /**
     * @param string $mysqlType
     *
     * @return bool Return true if $mysqlType is a json, false if not.
     */
    public function isJson($mysqlType)
    {
        return preg_match('~^json~', $mysqlType) === 1;
    }

    /**
     * @param string $mysqlType
     *
     * @return bool Return true if $mysqlType is a datetime, false if not.
     */
    private function isDateTime($mysqlType)
    {
        return preg_match('~^datetime|date|time|year|timestamp~', $mysqlType) === 1;
    }
}
