<?php namespace nyx\diagnostics;

// Internal dependencies
use nyx\diagnostics\debug\handlers;
use nyx\diagnostics\debug\interfaces;

/**
 * Debug
 *
 * Registers the Error and Exception Handlers contained within this component and gives static access to them.
 * Please note that the Handler can only be enabled once using self::enable() during script execution. If they
 * get unregistered from PHP you'll have to manually register the exact same instances again in order to avoid
 * potentially weird behaviour due to this class being completely static.
 *
 * Important note: Neither this class nor any of the Handlers will fiddle with your php.ini settings with regards
 * to error reporting, displaying errors etc. Please consult the Error Handler's docs {@see handlers\Error} for
 * more information on what the threshold means and how not setting any ini directives here affects its behaviour.
 *
 * @package     Nyx\Diagnostics\Debug
 * @version     0.0.3
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/diagnostics/index.html
 * @todo        Needs to seamlessly hook into already set error/exception handlers and handle less strict types
 *              than our handler interfaces just as well.
 * @todo        Move to Utils instead once properly abstracted?
 */
class Debug
{
    /**
     * @var bool                            Whether the Handlers already got registered (using this class) or not.
     */
    private static $enabled;

    /**
     * @var interfaces\handlers\Error       An Error Handler instance once registered.
     */
    private static $errorHandler;

    /**
     * @var interfaces\handlers\Exception   An Exception Handler instance once registered.
     */
    private static $exceptionHandler;

    /**
     * @var interfaces\Dumper               The Dumper in use.
     */
    private static $dumper;

    /**
     * Enables the bundled Error and Exception Handlers by registering them with PHP.
     *
     * Important note: The return values. When this method returns false, it merely means that the Handlers are
     * already registered and therefore could not be enabled again. This method will only return true for the call
     * that actually enables them. This is a little hack to make checking for certain conditions easier.
     *
     * @param   interfaces\handlers\Error       $error      An optional already instantiated Error Handler instance.
     *                                                      If none is given, a new one will be instantiated.
     * @param   interfaces\handlers\Exception   $exception  An optional already instantiated Exception Handler instance.
     *                                                      If none is given, a new one will be instantiated.
     * @param   int                             $threshold  {@see handlers\Error::setThreshold()}
     * @return  bool                                        True when Debug was not yet enabled, false otherwise.
     */
    public static function enable(interfaces\handlers\Error $error = null, interfaces\handlers\Exception $exception = null, $threshold = null) : bool
    {
        // Only enable the Handlers once. See the class description for more on this.
        if (static::$enabled) {
            return false;
        }

        // Register the Handlers.
        static::$errorHandler     = handlers\Error::register($error, $threshold);
        static::$exceptionHandler = handlers\Exception::register($exception);

        return static::$enabled = true;
    }

    /**
     * Dumps the given variable(s), providing information about their type, contents and others. Variadic, ie. accepts
     * multiple variables as parameters.
     *
     * @param   mixed[]     ...$vars    The variable(s) to dump info about.
     */
    public static function dump(...$vars)
    {
        // If we've got no Dumper specified, create the default one.
        if (null === static::$dumper) {
            static::setDumper(static::createDefaultDumper());
        }

        static::$dumper->dump(...$vars);
    }

    /**
     * Checks whether Debug is enabled.
     *
     * @return  bool    True when Debug is enabled, false otherwise.
     */
    public static function isEnabled() : bool
    {
        return true === static::$enabled;
    }

    /**
     * Returns the Error Handler in use.
     *
     * @return  interfaces\handlers\Error       The Error Handler in use, otherwise null if it has not been
     *                                          registered using self::enable().
     */
    public static function getErrorHandler() : interfaces\handlers\Error
    {
        return static::$errorHandler;
    }

    /**
     * Returns the Exception Handler in use.
     *
     * @return  interfaces\handlers\Exception   The Exception Handler in use, otherwise null if it has not been
     *                                          registered using self::enable().
     */
    public static function getExceptionHandler() : interfaces\handlers\Exception
    {
        return static::$exceptionHandler;
    }

    /**
     * Returns the Dumper in use.
     *
     * @return  interfaces\Dumper   The Dumper in use.
     */
    public static function getDumper() : interfaces\Dumper
    {
        return static::$dumper;
    }

    /**
     * Sets the Dumper to be used.
     *
     * @param   interfaces\Dumper   $dumper     The Dumper to be used.
     */
    public static function setDumper(interfaces\Dumper $dumper)
    {
        static::$dumper = $dumper;
    }

    /**
     * Returns the type of the given value - the class name for objects or the
     * type for all other types.
     *
     * @param   mixed   $value
     * @return  string
     */
    public static function getTypeName($value) : string
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }

    /**
     * Converts an Exception to an array in the format as returned by \Exception::getTrace().
     *
     * @param   \Throwable  $throwable  The Exception to convert.
     * @return  array
     */
    public static function throwableToArray(\Throwable $throwable) : array
    {
        return [
            'type'  => $throwable->getCode(),
            'file'  => $throwable->getFile(),
            'line'  => $throwable->getLine(),
            'class' => get_class($throwable),
            'args'  => [$throwable->getMessage()]
        ];
    }

    /**
     * Creates a default variable dumper to be used by self::dump() if no other has been set before the method
     * call to self::dump().
     *
     * @return  interfaces\Dumper|callable  $dumper     The created dumper.
     */
    protected static function createDefaultDumper()
    {
        return new debug\dumpers\Native;
    }
}
