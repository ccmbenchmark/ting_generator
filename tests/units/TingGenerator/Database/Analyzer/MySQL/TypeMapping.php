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

namespace tests\units\CCMBenchmark\TingGenerator\Database\Analyzer\MySQL;

use CCMBenchmark\TingGenerator\Infrastructure\PHPType;
use mageekguy\atoum;

/**
 * Class TypeMapping
 * @package tests\units\CCMBenchmark\TingGenerator\Database\Analyzer\MySQL
 * @tags MySQLTypeMapping
 */
class TypeMapping extends atoum
{
    public function testGetPhpTypeFromFieldTypeReturnInt()
    {
        $this
            ->given($this->newTestedInstance())
                ->string($this->testedInstance->getPhpTypeFromFieldType('tinyint'))
                    ->isIdenticalTo(PHPType::TYPE_INT)
                ->string($this->testedInstance->getPhpTypeFromFieldType('smallint'))
                    ->isIdenticalTo(PHPType::TYPE_INT)
                ->string($this->testedInstance->getPhpTypeFromFieldType('mediumint'))
                    ->isIdenticalTo(PHPType::TYPE_INT)
                ->string($this->testedInstance->getPhpTypeFromFieldType('int'))
                    ->isIdenticalTo(PHPType::TYPE_INT)
                ->string($this->testedInstance->getPhpTypeFromFieldType('bigint'))
                    ->isIdenticalTo(PHPType::TYPE_INT)
                ->string($this->testedInstance->getPhpTypeFromFieldType('bit'))
                    ->isIdenticalTo(PHPType::TYPE_INT)
                ->string($this->testedInstance->getPhpTypeFromFieldType('float'))
                    ->isIdenticalTo(PHPType::TYPE_INT)
                ->string($this->testedInstance->getPhpTypeFromFieldType('double'))
                    ->isIdenticalTo(PHPType::TYPE_INT)
                ->string($this->testedInstance->getPhpTypeFromFieldType('decimal'))
                    ->isIdenticalTo(PHPType::TYPE_INT);
    }

    public function testGetPhpTypeFromFieldTypeReturnStringForStringField()
    {
        $this
            ->given($this->newTestedInstance())
                ->string($this->testedInstance->getPhpTypeFromFieldType('char'))
                    ->isIdenticalTo(PHPType::TYPE_STRING)
                ->string($this->testedInstance->getPhpTypeFromFieldType('varchar'))
                    ->isIdenticalTo(PHPType::TYPE_STRING)
                ->string($this->testedInstance->getPhpTypeFromFieldType('tinytext'))
                    ->isIdenticalTo(PHPType::TYPE_STRING)
                ->string($this->testedInstance->getPhpTypeFromFieldType('text'))
                    ->isIdenticalTo(PHPType::TYPE_STRING)
                ->string($this->testedInstance->getPhpTypeFromFieldType('mediumtext'))
                    ->isIdenticalTo(PHPType::TYPE_STRING)
                ->string($this->testedInstance->getPhpTypeFromFieldType('longtext'))
                    ->isIdenticalTo(PHPType::TYPE_STRING)
                ->string($this->testedInstance->getPhpTypeFromFieldType('binary'))
                    ->isIdenticalTo(PHPType::TYPE_STRING)
                ->string($this->testedInstance->getPhpTypeFromFieldType('varbinary'))
                    ->isIdenticalTo(PHPType::TYPE_STRING)
                ->string($this->testedInstance->getPhpTypeFromFieldType('tinyblob'))
                    ->isIdenticalTo(PHPType::TYPE_STRING)
                ->string($this->testedInstance->getPhpTypeFromFieldType('blob'))
                    ->isIdenticalTo(PHPType::TYPE_STRING)
                ->string($this->testedInstance->getPhpTypeFromFieldType('mediumblob'))
                    ->isIdenticalTo(PHPType::TYPE_STRING)
                ->string($this->testedInstance->getPhpTypeFromFieldType('longblob'))
                    ->isIdenticalTo(PHPType::TYPE_STRING)
                ->string($this->testedInstance->getPhpTypeFromFieldType('enum'))
                    ->isIdenticalTo(PHPType::TYPE_STRING)
                ->string($this->testedInstance->getPhpTypeFromFieldType('set'))
                    ->isIdenticalTo(PHPType::TYPE_STRING);
    }

    public function testGetPhpTypeFromFieldTypeReturnStringForJsonField()
    {
        $this
            ->given($this->newTestedInstance())
                ->string($this->testedInstance->getPhpTypeFromFieldType('json'))
                    ->isIdenticalTo(PHPType::TYPE_STRING);
    }

    public function testGetPhpTypeFromFieldTypeReturnDateTime()
    {
        $this
            ->given($this->newTestedInstance())
                ->string($this->testedInstance->getPhpTypeFromFieldType('datetime'))
                    ->isIdenticalTo(PHPType::TYPE_DATETIME)
                ->string($this->testedInstance->getPhpTypeFromFieldType('date'))
                    ->isIdenticalTo(PHPType::TYPE_DATETIME)
                ->string($this->testedInstance->getPhpTypeFromFieldType('time'))
                    ->isIdenticalTo(PHPType::TYPE_DATETIME)
                ->string($this->testedInstance->getPhpTypeFromFieldType('year'))
                    ->isIdenticalTo(PHPType::TYPE_DATETIME)
                ->string($this->testedInstance->getPhpTypeFromFieldType('timestamp'))
                    ->isIdenticalTo(PHPType::TYPE_DATETIME)
        ;
    }

    public function testGetPhpTypeFromFieldTypeReturnNullForUnknownType()
    {
        $this
            ->given($this->newTestedInstance())
                ->variable($this->testedInstance->getPhpTypeFromFieldType(uniqid('type')))
                    ->isNull();
    }
}
