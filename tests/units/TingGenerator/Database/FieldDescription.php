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
 * Class FieldDescription
 * @package tests\units\CCMBenchmark\TingGenerator\Database
 * @tags FieldDescription
 */
class FieldDescription extends atoum
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $isPrimary;

    /**
     * @var bool
     */
    private $isAutoIncrement;

    /**
     * @param $method
     */
    public function beforeTestMethod($method)
    {
        $this->type = uniqid('type');
        $this->name = uniqid('name');
        $this->isPrimary = rand(0, 1) === 0;
        $this->isAutoIncrement = rand(0, 1) === 0;
    }

    /**
     * @return $this
     */
    private function createTestedInstance()
    {
        return $this
            ->given(
                $this->newTestedInstance($this->type, $this->name, $this->isPrimary, $this->isAutoIncrement)
            );
    }

    public function testGetType()
    {
        $this
            ->createTestedInstance()
            ->string($this->testedInstance->getType())
                ->isIdenticalTo($this->type);
    }

    public function testGetName()
    {
        $this
            ->createTestedInstance()
            ->string($this->testedInstance->getName())
            ->isIdenticalTo($this->name);
    }

    public function testIsPrimary()
    {
        $this
            ->createTestedInstance()
            ->boolean($this->testedInstance->isPrimary())
                ->isIdenticalTo($this->isPrimary);
    }

    public function testIsAutoIncrement()
    {
        $this
            ->createTestedInstance()
            ->boolean($this->testedInstance->isPrimary())
                ->isIdenticalTo($this->isAutoIncrement);
    }
}
