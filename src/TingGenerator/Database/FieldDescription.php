<?php

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
