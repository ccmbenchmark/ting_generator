<?php
/***********************************************************************
 *
 * Ting Generator
 * ==========================================
 *
 * Copyright (C) 2014 CCM Benchmark Group. (http://www.ccmbenchmark.com)
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

namespace CCMBenchmark\TingGenerator\Log;

class Logger
{
    /**
     * @param string $message
     */
    public function error($message)
    {
        echo "\n\033[31m $message \033[0m";
    }

    /**
     * @param string $message
     */
    public function warning($message)
    {
        echo "\n\033[33m $message \033[0m";
    }

    /**
     * @param string $message
     */
    public function info($message)
    {
        echo "\n\033[36m $message \033[0m";
    }
}
