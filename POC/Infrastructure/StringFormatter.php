<?php

namespace Infrastructure;

class StringFormatter
{
    public function camelize($input, $separator = '_')
    {
        return str_replace($separator, '', ucwords($input, $separator));
    }
}
