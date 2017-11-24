<?php

namespace CCMBenchmark\TingGenerator\Database\Analyzer;

interface TableAnalyzerInterface
{
    /**
     * @param string $databaseName
     * @param callable $excludedTablesFilter
     *
     * @return array
     */
    public function getTablesData($databaseName, callable $excludedTablesFilter = null);
}
