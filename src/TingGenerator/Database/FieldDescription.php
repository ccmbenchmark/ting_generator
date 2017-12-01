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

class FieldDescription
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
     * PropertyData constructor.
     * @param string $type
     * @param string $name
     * @param bool   $isPrimary
     * @param bool   $isAutoIncrement
     */
    public function __construct($type, $name, $isPrimary = false, $isAutoIncrement = false)
    {
        $this->type = (string) $type;
        $this->name = (string) $name;
        $this->isPrimary = (bool) $isPrimary;
        $this->isAutoIncrement = (bool) $isAutoIncrement;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isPrimary()
    {
        return $this->isPrimary;
    }

    /**
     * @return bool
     */
    public function isAutoIncrement()
    {
        return $this->isAutoIncrement;
    }
}
