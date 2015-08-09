<?php namespace nyx\diagnostics\debug\exceptions;

/**
 * Error Exception
 *
 * Special extension of the \ErrorException class used internally by Error Handlers to convert casual errors
 * into exceptions. Extends the base class with a (read-only) error context array which gets passed to a
 * registered error handler by PHP but which is unavailable in the base ErrorException class.
 *
 * @package     Nyx\Diagnostics\Debug
 * @version     0.0.5
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/diagnostics/debug.html
 */
class Error extends \ErrorException
{
    /**
     * @var array   The labels used for mapping error levels/types to human readable text.
     */
    protected static $labels = [
        E_WARNING           => 'Warning',
        E_NOTICE            => 'Notice',
        E_USER_ERROR        => 'User Error',
        E_USER_WARNING      => 'User Warning',
        E_USER_NOTICE       => 'User Notice',
        E_STRICT            => 'Runtime Notice',
        E_RECOVERABLE_ERROR => 'Catchable Fatal Error',
        E_DEPRECATED        => 'Deprecated',
        E_USER_DEPRECATED   => 'User Deprecated',
        E_ERROR             => 'Error',
        E_CORE_ERROR        => 'Core Error',
        E_COMPILE_ERROR     => 'Compile Error',
        E_PARSE             => 'Parse',
    ];

    /**
     * @var array   The error context.
     */
    private $context;

    /**
     * {@inheritDoc}
     *
     * @param   array   $context    An array containing all variables that existed in the scope the error was
     *                              triggered in.
     */
    public function __construct(string $message, int $code, int $type, string $file, string $line, array $context = [], \Exception $previous = null)
    {
        $this->context = $context;

        // Rewrite the message to make it more usable.
        $message = sprintf('%s: %s in %s line %d', isset(static::$labels[$type]) ? static::$labels[$type] : 'Unknown Error', $message, $file, $line);

        parent::__construct($message, $code, $type, $file, $line, $previous);
    }

    /**
     * Returns the error context.
     *
     * @return  array   The error context.
     */
    public function getContext() : array
    {
        return $this->context;
    }
}
