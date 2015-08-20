<?php namespace nyx\diagnostics\debug\interfaces\handlers;

/**
 * Exception Handler Interface
 *
 * @package     Nyx\Diagnostics\Debug
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/diagnostics/debug.html
 */
interface Exception
{
    /**
     * Handles an Exception.
     *
     * @param   \Throwable  $exception
     */
    public function handle(\Throwable $exception);
}
