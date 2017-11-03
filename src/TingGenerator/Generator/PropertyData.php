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
     * PropertyData constructor.
     * @param string $type
     * @param string $name
     */
    public function __construct($type, $name)
    {
        $this->type = (string) $type;
        $this->name = (string) $name;
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
}
