<?php namespace nyx\diagnostics\definitions;

/**
 * Diagnostics Signals Definition
 *
 * Signals should be returned as *bitmasks* to invoke the desired behaviour of the Handler. This is not necessary
 * for QUIT, as it includes PREVENT and STOP.
 *
 * @package     Nyx\Diagnostics\Definitions
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/diagnostics/debug/signals.html
 * @todo        Consider: Prevent handling Fatal Error Exceptions with PREVENT as well?
 */
final class Signals
{
    /**
     * The STOP signal is used for debugging purposes by Delegates and Conditions to inform the Handlers that
     * further Delegates and Conditions respectively should not be called/checked, but the interpreter should
     * be allowed to continue code execution.
     */
    const STOP = 1;

    /**
     * The PREVENT signal is used for debugging purposes by Conditions and:
     * a) in a Error Handler context will prevent the Error Handler from throwing the Exception it generated *but*
     *    it will force the Error Handler to return true as if it handled the Exception since Conditions don't get
     *    checked until the Handler determines that it applies to a given Error (when the error threshold is
     *    met etc.). This does not apply to Fatal Error Exceptions which will be passed to the
     *    Exception Handler regardless;
     * b) in a Exception Handler context will prevent the Exception Handler from invoking any Delegates.
     *
     * When used on its own it will allow all Conditions to be run and will only prevent any action afterwards.
     */
    const PREVENT = 2;

    /**
     * The QUIT signal is used for debugging purposes by Delegates and Conditions to inform the Handlers that
     * further code execution should cease immediately.
     *
     * Important note: QUIT includes PREVENT and STOP.
     */
    const QUIT = 3;
}
