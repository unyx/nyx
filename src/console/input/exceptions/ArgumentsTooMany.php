<?php namespace nyx\console\input\exceptions;

// Internal dependencies
use nyx\console\input\parameter\values\Arguments;

/**
 * Too Many Input Arguments Exception
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class ArgumentsTooMany extends InvalidArguments
{
    /**
     * {@inheritDoc}
     */
    public function __construct(Arguments $arguments, string $message = null, int $code = 0, \Exception $previous = null)
    {
        parent::__construct($arguments, $message ?? 'Too many arguments given.', $code, $previous);
    }
}
