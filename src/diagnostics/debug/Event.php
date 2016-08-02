<?php namespace nyx\diagnostics\debug;

// External dependencies
use nyx\events;

// Internal dependencies
use nyx\diagnostics\definitions;

/**
 * Debug Event
 *
 * Note: Setting the Throwable within a diagnostics\definitions\Events::DEBUG_EXCEPTION_AFTER Event has no effect
 * whatsoever.
 *
 * @package     Nyx\Diagnostics\Debug
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/diagnostics/debug.html
 */
class Event extends events\Event
{
    /**
     * @var \Throwable  The Throwable which is being handled.
     */
    private $throwable;

    /**
     * @var Handler     The Handler which emitted this Event.
     */
    private $handler;

    /**
     * {@inheritDoc}
     *
     * @param   \Exception  $throwable  The Throwable which is being handled.
     * @param   Handler     $handler    The Handler which emitted this Event.
     */
    public function __construct(\Throwable $throwable, Handler $handler, $name = null)
    {
        $this->throwable = $throwable;
        $this->handler   = $handler;

        parent::__construct($name);
    }

    /**
     * Returns the Throwable which is being handled.
     *
     * @return  \Exception
     */
    public function getThrowable() : \Throwable
    {
        return $this->throwable;
    }

    /**
     * Sets the Throwable to be handled.
     *
     * @param   \Throwable  $throwable
     * @return  $this
     */
    public function setThrowable(\Throwable $throwable) : Event
    {
        // Setting the Exception after it's already been handled makes no sense. This might potentially lead to
        // confusion in the future as we are not throwing an Exception on our own for this in order not to mess
        // up things while already within an Exception Handler context.
        if ($this->getName() !== definitions\Events::DEBUG_THROWABLE_AFTER) {
            $this->throwable = $throwable;
        }

        return $this;
    }

    /**
     * Returns the Handler which emitted this Event.
     *
     * @return  Handler
     */
    public function getHandler()
    {
        return $this->handler;
    }
}
