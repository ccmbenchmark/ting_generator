<?php

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
