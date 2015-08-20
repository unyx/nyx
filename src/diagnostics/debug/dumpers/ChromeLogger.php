<?php namespace nyx\diagnostics\debug\dumpers;

// Internal dependencies
use nyx\diagnostics\debug\interfaces;

/**
 * Chrome Logger Dumper
 *
 * A bridge allowing to use Chrome Logger as a Dumper within the Debug subcomponent. Check out Chrome Logger itself
 * on {@see http://craig.is/writing/chrome-logger} and its PHP variant on Github
 * {@see https://github.com/ccampbell/chromephp}.
 *
 * Requires:
 * - Package: ccampbell/chromephp (available as suggestion for nyx/diagnostics within Composer)
 *
 * @package     Nyx\Diagnostics\Debug
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/diagnostics/debug.html
 */
class ChromeLogger implements interfaces\Dumper
{
    /**
     * {@inheritDoc}
     */
    public function dump(...$vars)
    {
        call_user_func_array('ChromePhp::log', $vars);
    }
}
