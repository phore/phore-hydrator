<?php


namespace Phore\Hydrator\Ex;


class UnrecognizedKeyException extends HydratorInputDataException
{
    public function __construct(array $path, $key)
    {
        $message = "Unrecognized key '$key' in input data at ";
        $message .= implode(".", $path);
        parent::__construct($message, 0, null);
    }
}
