<?php namespace nyx\diagnostics\debug;

// Internal dependencies
use nyx\diagnostics;

/**
 * Throwable Inspector
 *
 * The Throwable being inspected and the (optional) Handler are immutable. If you do not set the Handler during
 * construction of the Inspector, it will not be set for the whole lifecycle of the Inspector.
 *
 * @package     Nyx\Diagnostics\Debug
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/diagnostics/debug.html
 * @todo        Handle previous exceptions (instantiate Inspectors for them on the fly while creating the Trace?).
 */
class Inspector
{
    /**
     * @var \Throwable  The Throwable that is being inspected.
     */
    private $throwable;

    /**
     * @var Trace       A Trace instance.
     */
    private $trace;

    /**
     * @var handlers\Exception  The Exception Handler currently handling the inspected Throwable.
     */
    private $handler;

    /**
     * Prepares a new Inspector by feeding him a Throwable that shall be inspected and the Handler which
     * started the inspection.
     *
     * @param   \Throwable         $throwable The Throwable that is to be inspected.
     * @param   handlers\Exception $handler   The Handler which started the inspection, if available.
     */
    public function __construct(\Throwable $throwable, handlers\Exception $handler = null)
    {
        $this->throwable = $throwable;
        $this->handler   = $handler;
    }

    /**
     * Returns the Throwable currently being inspected.
     *
     * @return  \Throwable  The Throwable being inspected.
     */
    public function getThrowable() : \Throwable
    {
        return $this->throwable;
    }

    /**
     * Returns the Exception Handler currently handling the inspected Throwable, if available.
     *
     * @return  handlers\Exception|null
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Returns a Trace instance representing the stack trace of the inspected Throwable.
     *
     * If the trace contains a handlers\Error entry (indicating the Throwable is the result of an internal
     * error -> exception conversion), that frame will be removed. If it does not, a frame for the actual Throwable
     * will be prepended to the frame stack instead, to make it easier to iterate over the causality chain.
     *
     * @return  Trace
     */
    public function getTrace() : Trace
    {
        // No need for further magic if we've already instantiated a Trace Sequence.
        if ($this->trace !== null) {
            return $this->trace;
        }

        // Instantiate a new Trace Sequence and cache it locally.
        return $this->trace = new Trace($this->getFrames());
    }

    /**
     * Returns the traced frames from the inspected Throwable..
     *
     * @return  array
     */
    protected function getFrames() : array
    {
        $frames = $this->throwable->getTrace();

        if (!$this->throwable instanceof \ErrorException) {

            // Make sure the first frame in the trace actually points to the Throwable we're inspecting.
            array_unshift($frames, diagnostics\Debug::throwableToArray($this->throwable));

            return $frames;
        }

        // We're going to determine if the exception in fact stems from an irrecoverable fatal error.
        $fatal = false;

        // Note: E_RECOVERABLE_ERROR and E_PARSE should as of PHP7 be entirely converted to Throwables
        // so they should be caught by an Exception Handler directly instead of first going through an Error Handler
        // and being converted to an Error Exception, let alone a FatalError Exception. We still keep
        // them here for edge cases, however.
        switch ($this->throwable->getSeverity()) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
            case E_PARSE:
                $fatal = true;
            break;
        }

        // Without XDebug we don't actually have any means to determine the stacktrace of a fatal error.
        if (!$fatal || !extension_loaded('xdebug') || !xdebug_is_enabled()) {
            // Our error handler will be the first frame in the trace since it's the one which converted
            // the error to an exception. We're gonna copy over the context and remove
            // Remove our (error) handler from the stack trace (it's otherwise always going to occlude
            // the actual exception).
            // $frames[1]['args'] = $frames[0]['args'];
            array_shift($frames);

            return $frames;
        }

        // Remove our internal handling logic from the stack trace so it doesn't occlude the actual trace.
        $frames = array_diff_key(array_reverse(xdebug_get_function_stack()), debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));

        // Handle some potential inconsistencies between XDebug and the way we want to handle things.
        foreach ($frames as &$frame) {

            if ('dynamic' === $frame['type']) {
                $frame['type'] = '->';
            } elseif ('static' === $frame['type']) {
                $frame['type'] = '::';
            }

            // XDebug uses a different key for the args array.
            if (isset($frame['params']) && !isset($frame['args'])) {
                $frame['args'] = $frame['params'];
                unset($frame['params']);
            }
        }

        return $frames;
    }
}
