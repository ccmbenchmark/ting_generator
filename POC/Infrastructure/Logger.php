<?php

namespace Infrastructure;

class Logger
{
    /**
     * @param string $message
     *
     * @return $this
     */
    public function info($message)
    {
        echo "\n" . $message;

        return $this;
    }
}
