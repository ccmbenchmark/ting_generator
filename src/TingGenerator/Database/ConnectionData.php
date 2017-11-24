<?php

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
