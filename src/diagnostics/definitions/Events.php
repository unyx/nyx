<?php namespace nyx\diagnostics\definitions;

/**
 * Diagnostics Events Definition
 *
 * @package     Nyx\Diagnostics\Definitions
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/diagnostics/events.html
 * @todo        Consider: Emit events also after conditions have been checked?
 */
final class Events
{
    /**
     * The DEBUG_EXCEPTION_BEFORE event is triggered *before* an Exception Handler handles an Exception and
     * *before* Conditions get checked.
     *
     * Listeners registered for this event will receive a {@see \nyx\diagnostics\debug\Event} event
     * instance as their first parameter.
     */
    const DEBUG_EXCEPTION_BEFORE = 'diagnostics.debug.exception.before';

    /**
     * The DEBUG_EXECUTION_AFTER event is triggered *after* an Exception Handler handles an Exception but before
     * the code execution stops (regardless of whether a Delegate returns a QUIT signal, meaning a listener may
     * indeed override said signal by using setAllowQuit(false) on the handler attached to the event instance they
     * will be given).
     *
     * Listeners registered for this event will receive a {@see \nyx\diagnostics\debug\Event} event
     * instance as their first parameter.
     */
    const DEBUG_EXCEPTION_AFTER = 'diagnostics.debug.exception.after';

    /**
     * The DEBUG_ERROR_BEFORE event is triggered *after* an Error Handler catches an error it is responsible for
     * and is about to handle it by throwing it as an Error Exception, but *before* Conditions get checked.
     *
     * Note: This Event is emitted both for normal Error Exception *and* for FatalError Exceptions. In case of
     * fatal errors, it will be emitted *after* the DEBUG_FATAL_ERROR_BEFORE event.
     *
     * Listeners registered for this event will receive a {@see \nyx\diagnostics\debug\Event} event
     * instance as their first parameter.
     */
    const DEBUG_ERROR_BEFORE = 'diagnostics.debug.error.before';

    /**
     * The DEBUG_FATAL_ERROR_BEFORE event is triggered *after* a FatalError Handler catches a fatal error it is
     * responsible for and is about to handle it by throwing it as an FatalError Exception, but *before* Conditions
     * get checked.
     *
     * Listeners registered for this event will receive a {@see \nyx\diagnostics\debug\Event} event
     * instance as their first parameter.
     */
    const DEBUG_FATAL_ERROR_BEFORE = 'diagnostics.debug.fatalError.before';
}
