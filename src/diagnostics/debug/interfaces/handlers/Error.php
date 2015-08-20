<?php namespace nyx\diagnostics\debug\interfaces\handlers;

/**
 * Error Handler Interface
 *
 * @package     Nyx\Diagnostics\Debug
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/diagnostics/debug.html
 */
interface Error
{
    /**
     * Handles a PHP error. The parameters are inline with those passed to a registered (set_error_handler()) error
     * handler.
     *
     * @param   int     $type       The error type (severity).
     * @param   string  $message    The error message.
     * @param   string  $file       The filename (path) in which the error occurred.
     * @param   int     $line       The line in which the error occurred.
     * @param   array   $context    An array containing all variables that existed in the scope the error was
     *                              triggered in.
     * @return  bool                True when the error got handled, false otherwise.
     */
    public function handle(int $type, string $message, string $file = null, int $line = null, array $context = []) : bool;
}
