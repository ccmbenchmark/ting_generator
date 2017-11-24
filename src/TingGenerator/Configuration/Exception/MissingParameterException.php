<?php

namespace CCMBenchmark\TingGenerator\Configuration\Exception;

use Throwable;

class MissingParameterException extends \InvalidArgumentException
{
    /**
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @return MissingParameterException
     */
    public static function fromMissingParameterInConfiguration($message = "", $code = 0, Throwable $previous = null)
    {
        return new self($message, $code, $previous);
    }
}
