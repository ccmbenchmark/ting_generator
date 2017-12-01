<?php

namespace CCMBenchmark\TingGenerator\Database\Analyzer;

use CCMBenchmark\TingGenerator\Database\TableDescription;

interface TableAnalyzerInterface
{
    /**
     * @param string $databaseName
     * @param callable $excludedTablesFilter
     *
     * @return TableDescription[]
     */
    public function getTablesData($databaseName, callable $excludedTablesFilter = null);
}
