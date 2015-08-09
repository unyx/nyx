<?php namespace nyx\diagnostics\debug\exceptions\fatal;

// Internal dependencies
use nyx\diagnostics\debug\exceptions;

/**
 * Undefined Function Fatal Error Exception
 *
 * @package     Nyx\Diagnostics\Debug
 * @version     0.0.5
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/diagnostics/debug.html
 */
class UndefinedFunction extends exceptions\FatalError
{
    /**
     * {@inheritDoc}
     */
    public function __construct(string $message, \ErrorException $previous)
    {
        parent::__construct($message, $previous->getCode(), $previous->getSeverity(), $previous->getFile(), $previous->getLine(), $previous->getPrevious());
    }
}
