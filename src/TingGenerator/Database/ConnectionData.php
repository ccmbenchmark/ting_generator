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

class ConnectionData
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $userName;

    /**
     * @var string
     */
    private $password;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $charset;

    /**
     * @var string
     */
    private $database;

    /**
     * @param string $type
     * @param string $host
     * @param string $userName
     * @param string $password
     * @param int $port
     * @param string $charset
     * @param string $database
     */
    public function __construct(
        $type,
        $host,
        $userName,
        $password,
        $port,
        $charset,
        $database
    ) {
        $this->type = (string) $type;
        $this->host = (string) $host;
        $this->userName = (string) $userName;
        $this->password = (string) $password;
        $this->port = (int) $port;
        $this->charset = (string) $charset;
        $this->database = (string) $database;
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
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @return string
     */
    public function getDatabase()
    {
        return $this->database;
    }
}
