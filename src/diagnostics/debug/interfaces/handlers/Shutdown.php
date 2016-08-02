<?php namespace nyx\diagnostics\debug\interfaces\handlers;

/**
 * Fatal Error Handler Interface
 *
 * @package     Nyx\Diagnostics\Debug
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/diagnostics/debug.html
 * @todo        Move into core as OnShutdown interface?
 */
interface Shutdown
{
    /**
     * Handles error-related cleanups. Intended to be registered as shutdown function with PHP. Implementations
     * should attempt to check if any error occurred before shutdown and if so, determine whether it was a fatal
     * error and handle it gracefully.
     */
    public function onShutdown();
}
