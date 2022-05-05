<?php


namespace Phore\Hydrator\Ex;


class InvalidStructureException extends HydratorInputDataException
{

    public function __construct(array $path=[], $expected=null, $found=null, $typedef=null)
    {
        
        $message = "Invalid input data at ";
        $message .= implode(".", $path);
        $message .= ": Expected type '$expected' - found: '" . ($typedef !== null ? $typedef : gettype($found)) . "'";
        parent::__construct($message, 0, null);
    }

}
