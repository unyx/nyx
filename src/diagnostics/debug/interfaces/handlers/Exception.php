<?php namespace nyx\diagnostics\debug\interfaces\handlers;

/**
 * Exception Handler Interface
 *
 * Note: Technically this should be a Throwable Handler Interface, but is left as is for consistency with
 *       set_exception_handler() which did not get renamed with the introduction of Throwables in PHP7.
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
     * Handles a Throwable.
     *
     * @param   \Throwable  $throwable
     */
    public function handle(\Throwable $throwable);
}
