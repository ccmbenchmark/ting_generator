<?php
/***********************************************************************
 *
 * Ting Generator
 * ==========================================
 *
 * Copyright (C) 2017 CCM Benchmark Group. (http://www.ccmbenchmark.com)
 *
 ***********************************************************************
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you
 * may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
 * implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 **********************************************************************/

namespace CCMBenchmark\TingGenerator\Database\Analyzer\MySQL;

use CCMBenchmark\TingGenerator\Database\Analyzer\TypeMapperInterface;
use CCMBenchmark\TingGenerator\Infrastructure\PHPType;

class TypeMapping implements TypeMapperInterface
{
    /**
     * @param string $mysqlType
     *
     * @return null|string
     */
    public function getFromMysqlType($mysqlType)
    {
        $mysqlType = trim(mb_strtolower($mysqlType));

        if ($this->isInteger($mysqlType) === true) {
            return PHPType::TYPE_INT;
        }

        if ($this->isString($mysqlType) === true || $this->isJson($mysqlType) === true) {
            return PHPType::TYPE_STRING;
        }

        if ($this->isDateTime($mysqlType) === true) {
            return PHPType::TYPE_DATETIME;
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
        return preg_match(
            '~^(?:tinyint|smallint|mediumint|int|bigint|bit|float|double|decimal)(?:\([0-9]+\))?$~',
            $mysqlType
        ) === 1;
    }

    /**
     * @param string $mysqlType
     *
     * @return bool Return true if $mysqlType is a string, false if not.
     */
    private function isString($mysqlType)
    {
        return preg_match(
            '~^(?:char|varchar|tinytext|text|mediumtext|longtext|json|binary|varbinary|tinyblob|'
                . 'blob|mediumblob|longblob|enum|set)(?:\([0-9]+\))?$~',
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
        return preg_match('~^json(?:\([0-9]+\))?$~', $mysqlType) === 1;
    }

    /**
     * @param string $mysqlType
     *
     * @return bool Return true if $mysqlType is a datetime, false if not.
     */
    private function isDateTime($mysqlType)
    {
        return preg_match('~^(?:datetime|date|time|year|timestamp)(?:\([0-9]+\))?$~', $mysqlType) === 1;
    }
}
