<?php

namespace CCMBenchmark\TingGenerator\Infrastructure;

class StringFormatter
{
    /**
     * @param string $input
     * @param string $separator
     *
     * @return string
     */
    public function camelize($input, $separator = '_')
    {
        return (string) str_replace($separator, '', ucwords($input, $separator));
    }
}
