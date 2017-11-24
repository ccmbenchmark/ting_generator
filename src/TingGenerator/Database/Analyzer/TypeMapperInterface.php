<?php

namespace CCMBenchmark\TingGenerator\Database\Analyzer;

interface TypeMapperInterface
{
    /**
     * @param string $mysqlType
     *
     * @return null|string
     */
    public function getFromMysqlType($mysqlType);
}
