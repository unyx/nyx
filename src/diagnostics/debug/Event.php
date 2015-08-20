<?php namespace nyx\diagnostics\debug;

// External dependencies
use nyx\events;

// Internal dependencies
use nyx\diagnostics\definitions;

/**
 * Debug Event
 *
 * Note: Setting the Exception within a diagnostics\definitions\Events::DEBUG_EXCEPTION_AFTER Event has no effect
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
     * @var \Exception  The Exception which is being handled.
     */
    private $exception;

    /**
     * @var Handler     The Handler which emitted this Event.
     */
    private $handler;

    /**
     * {@inheritDoc}
     *
     * @param   \Exception      $exception  The Exception which is being handled.
     * @param   Handler         $handler    The Handler which emitted this Event.
     */
    public function __construct(\Exception $exception, Handler $handler, $name = null)
    {
        $this->exception = $exception;
        $this->handler   = $handler;

        parent::__construct($name);
    }

    /**
     * Returns the Exception which is being handled.
     *
     * @return  \Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * Sets the Exception to be handled.
     *
     * @param   \Exception  $exception
     * @return  $this
     */
    public function setException(\Exception $exception)
    {
        // Setting the Exception after it's already been handled makes no sense. This might potentially lead to
        // confusion in the future as we are not throwing an Exception on our own for this in order not to mess
        // up things while already within an Exception Handler context.
        if ($this->getName() !== definitions\Events::DEBUG_EXCEPTION_AFTER) {
            $this->exception = $exception;
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
