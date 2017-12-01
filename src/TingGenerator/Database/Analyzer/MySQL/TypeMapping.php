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
     * @param string $fieldType
     *
     * @return null|string
     */
    public function getPhpTypeFromFieldType($fieldType)
    {
        $fieldType = trim(mb_strtolower($fieldType));

        if ($this->isInteger($fieldType) === true) {
            return PHPType::TYPE_INT;
        }

        if ($this->isString($fieldType) === true || $this->isJson($fieldType) === true) {
            return PHPType::TYPE_STRING;
        }

        if ($this->isDateTime($fieldType) === true) {
            return PHPType::TYPE_DATETIME;
        }

        return null;
    }

    /**
     * @param string $fieldType
     *
     * @return bool Return true if $fieldType is an integer, false if not.
     */
    private function isInteger($fieldType)
    {
        return preg_match(
            '~^(?:tinyint|smallint|mediumint|int|bigint|bit|float|double|decimal)(?:\([0-9]+\))?$~',
            $fieldType
        ) === 1;
    }

    /**
     * @param string $fieldType
     *
     * @return bool Return true if $fieldType is a string, false if not.
     */
    private function isString($fieldType)
    {
        return preg_match(
            '~^(?:char|varchar|tinytext|text|mediumtext|longtext|json|binary|varbinary|tinyblob|'
                . 'blob|mediumblob|longblob|enum|set)(?:\([0-9]+\))?$~',
            $fieldType
        ) === 1;
    }

    /**
     * @param string $fieldType
     *
     * @return bool Return true if $fieldType is a json, false if not.
     */
    public function isJson($fieldType)
    {
        return preg_match('~^json(?:\([0-9]+\))?$~', $fieldType) === 1;
    }

    /**
     * @param string $fieldType
     *
     * @return bool Return true if $fieldType is a datetime, false if not.
     */
    private function isDateTime($fieldType)
    {
        return preg_match('~^(?:datetime|date|time|year|timestamp)(?:\([0-9]+\))?$~', $fieldType) === 1;
    }
}
