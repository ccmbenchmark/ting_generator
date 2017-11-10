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
     * PropertyData constructor.
     * @param string $type
     * @param string $name
     * @param bool   $isPrimary
     */
    public function __construct($type, $name, $isPrimary = false)
    {
        $this->type = (string) $type;
        $this->name = (string) $name;
        $this->isPrimary = (bool) $isPrimary;
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
}
