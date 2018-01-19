<?php
/***********************************************************************
 *
 * Ting Generator for entity.
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

namespace tests\units\CCMBenchmark\TingGenerator\Generator;

use mageekguy\atoum;
use Zend\Code\Generator\ClassGenerator;

/**
 * Class ClassGeneratorFactory
 * @package tests\units\CCMBenchmark\TingGenerator\Generator
 * @tags ClassGeneratorFactory
 */
class ClassGeneratorFactory extends atoum
{
    public function testGetReturnClassGenerator()
    {
        $this
            ->given($this->newTestedInstance())
            ->object($this->testedInstance->get())
                ->isInstanceOf(ClassGenerator::class);
    }
}
