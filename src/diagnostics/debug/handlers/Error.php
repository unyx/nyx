<?php namespace nyx\diagnostics\debug\handlers;

// Internal dependencies
use nyx\diagnostics\debug\exceptions;
use nyx\diagnostics\debug\interfaces;
use nyx\diagnostics\debug;
use nyx\diagnostics\definitions;

/**
 * Error Handler
 *
 * Converts errors of a severity equal to or above the given threshold into Exceptions for easier inspections
 * and more robust and cohesive handling. Does not modify any of the input data it gets (for instance, error
 * messages are left as is, so the Exception Handler can decide how to present them).
 *
 * Note: The internal error threshold {@see self::setThreshold()} can work decoupled from PHP's error_reporting()
 *       level. For instance, if PHP is set to report all errors but this handler's threshold is set to warnings,
 *       only warnings and higher levels of errors will get converted to exceptions. This handler *does not*
 *       automatically turn off PHP's display_errors ini directive, meaning that under the above circumstances
 *       all errors below warnings might get displayed by PHP's internal error handler unless you set the
 *       directive to 0 yourself.
 *
 * @package     Nyx\Diagnostics\Debug
 * @version     0.0.5
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/diagnostics/debug.html
 */
class Error extends debug\Handler implements interfaces\handlers\Error, interfaces\handlers\Shutdown
{
    /**
     * @var int     The error severity required to convert an error into an Exception.
     */
    private $threshold;

    /**
     * @var int     The error severity this Handler has been initialized with.
     */
    private $initialThreshold;

    /**
     * Registers the given or a new Error Handler with PHP.
     *
     * @param   interfaces\handlers\Error   $handler    An optional, already instantiated Error handler instance.
     *                                                  If none is given, a new one will be instantiated.
     * @param   int                         $threshold  {@see self::setThreshold()} Will be ignored if you provide
     *                                                  your own Error Handler instance.
     * @return  Error                                   An instance of the error handler which got registered.
     *                                                  Either the same as the given one or if none was given,
     *                                                  a new instance.
     */
    public static function register(interfaces\handlers\Error $handler = null, int $threshold = null)
    {
        // Use the given handler or instantiate a new one?
        $handler = $handler ?: new static($threshold);

        // Note: Utilizing the error_types parameter of set_error_handler would probably simplify this handler
        // a little bit (and make it perform better due to a cutdown on error_reporting() calls in cases with
        // lots of errors reported but ignored here due to being below threshold) but setting the threshold
        // on the fly would become unpredictable.
        set_error_handler([$handler, 'handle']);

        if ($handler instanceof interfaces\handlers\Shutdown) {
            register_shutdown_function([$handler, 'onShutdown']);
        }

        return $handler;
    }

    /**
     * Constructs a new Error Handler instance.
     *
     * @param   int $threshold      {@see self::setThreshold()}.
     */
    public function __construct(int $threshold = null)
    {
        $this->setThreshold($threshold);

        // Keep track of the initial threshold so we can easily restore it later.
        $this->initialThreshold = $this->threshold;
    }

    /**
     * Returns the error severity required to convert an error into an Exception.
     *
     * @return  int
     */
    public function getThreshold() : int
    {
        return $this->threshold;
    }

    /**
     * Sets the error severity required to convert an error into an Exception.
     *
     * @param   int $threshold      The threshold. Passing null will make the handler use the current
     *                              error_reporting() level as reported by PHP.
     * @return  $this
     */
    public function setThreshold(int $threshold = null) : self
    {
        $this->threshold = null === $threshold ? error_reporting() : $threshold;

        return $this;
    }

    /**
     * Sets the current threshold to the level this Handler has been initialized with.
     *
     * @return  $this
     */
    public function restoreThreshold() : self
    {
        return $this->setThreshold($this->initialThreshold);
    }

    /**
     * {@inheritDoc}
     *
     * Converts errors of a severity equal to or above the given threshold into Exceptions for easier inspections
     * and more robust and cohesive handling. Ignores errors which do not meet the thresholds and returns false
     * for those instead, letting PHP's internal error handling handle such a case.
     *
     * @throws  exceptions\Error    When the given error severity meets both the error_reporting() and internal
     *                              thresholds.
     */
    public function handle(int $type, string $message, string $file = null, int $line = null, array $context = []) : bool
    {
        // A threshold of 0 means the handler should ignore all errors.
        if (0 === $this->threshold) {
            return false;
        }

        // Make sure that the severity is included in the error_reporting level (the handler will get called for
        // nearly every error regardless of the error_reporting setting) and that it fits our threshold as well.
        if (error_reporting() & $type and $this->threshold & $type) {

            // We will construct an Exception but won't throw it yet. Conditions might prevent us from doing it.
            $exception = new exceptions\Error($message, 0, $type, $file, $line, $context);

            // Being Emitter Aware we are bound to comply to the Events Definition.
            // self::emitDebugEvent() will return false when no Emitter is present. Otherwise we'll get the
            // Exception after it's been processed by Event Listeners so we need to overwrite it here.
            if (null !== $response = $this->emitDebugEvent(definitions\Events::DEBUG_ERROR_BEFORE, $exception)) {
                $exception = $response;
            }

            // First of all run all Conditions. The method will return true if we are to prevent throwing
            // the Exception and since technically this Handler *somehow* handled the situation, we will return
            // true so PHP knows about it.
            if ($this->runConditions($exception)) {
                return true;
            }

            // Seems we weren't prevented, so let's do eet.
            throw $exception;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     *
     * Attempts to catch the last error which occurred during script execution, convert it to an exception and
     * pass it to an Exception Handler that is known and usable by this class.
     *
     * @throws  exceptions\FatalError   When a fatal error meeting the threshold conditions has occurred.
     */
    public function onShutdown()
    {
        // Since this gets registered as a shutdown function, upon shutdown we need to check whether an error
        // actually occurred.
        if (null === $error = error_get_last()) {
            return;
        }

        // Even if the handler is set to ignore all errors, we are still going to kick in for the most fundamental
        // errors reported by PHP.
        if (0 === $this->threshold && !$this->isFatal($error['type'])) {
            return;
        }

        // Instead of directly coupling this with our own exception handler, we'll try to see if an exception handler
        // is already registered...
        $handler = set_exception_handler(null);

        // ... and then check if it's an instance of our own exception handler so that we can make use of it.
        // BTW Why isn't such a basic Exception Handler Interface a PSR yet?
        if (is_array($handler) && $handler[0] instanceof interfaces\handlers\Exception) {

            // We will construct an Exception as we need to pass it to our Conditions.
            $exception = new exceptions\FatalError($error['message'], 0, $error['type'], $error['file'], $error['line']);

            // Being Emitter Aware we are bound to comply to the Events Definition.
            // self::emitDebugEvent() will return false when no Emitter is present. Otherwise we'll get the
            // Exception after it's been processed by Event Listeners so we need to overwrite it here.
            if (null !== $response = $this->emitDebugEvent(definitions\Events::DEBUG_FATAL_ERROR_BEFORE, $exception)) {
                // Now, as per Events Definition, also emit a casual DEBUG_ERROR_BEFORE event. No need to check
                // for an Emitter now anymore, obviously.
                $exception = $this->emitDebugEvent(definitions\Events::DEBUG_ERROR_BEFORE, $response);
            }

            // Run the Conditions but ignore the PREVENT signal.
            $this->runConditions($exception);

            // Pass the Exception to the Exception Handler.
            /** @var interfaces\handlers\Exception[] $handler */
            $handler[0]->handle($exception);
        }

        exit;
    }

    /**
     * Determines if given error type is considered a fatal error.
     *
     * Note: E_RECOVERABLE_ERROR and E_PARSE should as of PHP7 be entirely converted to Throwables so they
     * should be caught by an Exception Handler directly instead of first going through an Error Handler
     * and being converted to an Error Exception, let alone a FatalError Exception. We still keep them here
     * for edge cases, however.
     *
     * @param   int     $type   The error type to check.
     * @return  bool
     */
    protected function isFatal(int $type)
    {
        return in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR, E_PARSE]);
    }
}
