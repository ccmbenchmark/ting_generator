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

namespace CCMBenchmark\TingGenerator\Database;

class TableDescription
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
     * TableDescription constructor.
     * @param string $name
     * @param array $fieldsDescription
     */
    public function __construct($name, array $fieldsDescription)
    {
        $this->name = (string) $name;
        $this->fieldsDescription = $fieldsDescription;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getFieldsDescription()
    {
        return $this->fieldsDescription;
    }
}
