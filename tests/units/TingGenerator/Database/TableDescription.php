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

namespace tests\units\CCMBenchmark\TingGenerator\Database;

use mageekguy\atoum;

/**
 * Class TableDescription
 * @package tests\units\CCMBenchmark\TingGenerator\Database
 * @tags TableDescription
 */
class TableDescription extends atoum
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $fieldsDescription;

    /**
     * @param string $testMethod
     */
    public function beforeTestMethod($testMethod)
    {
        $this->name = uniqid('name');

        $this->fieldsDescription = [
            uniqid('key') => uniqid('value'),
            uniqid('key') => uniqid('value'),
            uniqid('key') => uniqid('value')
        ];
    }

    public function testGetName()
    {
        $this
            ->given($this->newTestedInstance($this->name, $this->fieldsDescription))
            ->string($this->testedInstance->getName())
                ->isIdenticalTo($this->name);
    }

    public function testGetFieldsDescription()
    {
        $this
            ->given($this->newTestedInstance($this->name, $this->fieldsDescription))
                ->array($this->testedInstance->getFieldsDescription())
                    ->isIdenticalTo($this->fieldsDescription);
    }
}
