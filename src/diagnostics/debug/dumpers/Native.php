<?php namespace nyx\diagnostics\debug\dumpers;

// Internal dependencies
use nyx\diagnostics\debug\interfaces;

/**
 * Native Dumper
 *
 * Uses var_dump() to perform the dump.
 *
 * @package     Nyx\Diagnostics\Debug
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/diagnostics/debug.html
 */
class Native implements interfaces\Dumper
{
    /**
     * {@inheritDoc}
     */
    public function dump(...$vars)
    {
        call_user_func('var_dump', ...$vars);
    }
}
