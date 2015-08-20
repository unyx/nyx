<?php namespace nyx\diagnostics\debug\interfaces;

// Internal dependencies
use nyx\diagnostics\debug;

/**
 * Delegate Interface
 *
 * A Delegate is an Exception Handler which relies on an Inspector to gather all particular data about an
 * Exception and then processes that data. When used with the main Exception Handler contained within this package,
 * Delegates can be stacked and will handle an Exception in the order of priority they get registered with, unless
 * (until) one of them returns one of the signals defined in \nyx\diagnostics\definitions\Signals.
 *
 * @package     Nyx\Diagnostics\Debug
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/diagnostics/debug.html
 */
interface Delegate
{
    /**
     * Handles an exception by examining the data an Inspector gathered about it and acting based on that data
     * in whatever way the Delegate sees fit.
     *
     * @param   debug\Inspector     $inspector  An Inspector instance containing all the relevant information
     *                                          about the Exception being handled.
     * @return  int|null                        Either a bitmask of the Signals defined in definitions\Signals
     *                                          or null.
     */
    public function handle(debug\Inspector $inspector);
}
