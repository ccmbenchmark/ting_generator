<?php

namespace CCMBenchmark\TingGenerator\Generator;

class PropertyData
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
     * @var string
     */
    private $phpType;

    /**
     * PropertyData constructor.
     * @param string $type
     * @param string $name
     * @param string $phpType
     */
    public function __construct($type, $name, $phpType)
    {
        $this->type = (string) $type;
        $this->name = (string) $name;
        $this->phpType = (string) $phpType;
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
     * @return string
     */
    public function getPhpType()
    {
        return $this->phpType;
    }
}
