<?php namespace nyx\console\input\exceptions;

// Internal dependencies
use nyx\console\input\parameter\values\Arguments;

/**
 * Not Enough Input Arguments Exception
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class ArgumentsNotEnough extends InvalidArguments
{
    /**
     * {@inheritDoc}
     */
    public function __construct(Arguments $arguments, $message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct($arguments, $message ?? 'Not enough arguments given. Got '.$arguments->count().', expected at least '.$arguments->definitions()->required(), $code, $previous);
    }
}
