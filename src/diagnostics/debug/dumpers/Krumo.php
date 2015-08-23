<?php namespace nyx\diagnostics\debug\dumpers;

// Internal dependencies
use nyx\diagnostics\debug\interfaces;

/**
 * Krumo Dumper
 *
 * A bridge allowing to use Krumo as a Dumper within the Debug subcomponent.
 *
 * Important note: This bridge relies on the krumo class being available, but it is not a Composer package. Please
 * take a look at {@see http://krumo.sourceforge.net} on how to install and load it.
 *
 * @package     Nyx\Diagnostics\Debug
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/diagnostics/debug.html
 */
class Krumo implements interfaces\Dumper
{
    /**
     * {@inheritDoc}
     */
    public function dump(...$vars)
    {
        // @todo Actual output handling.
        echo call_user_func('krumo::dump', ...$vars);
    }
}
