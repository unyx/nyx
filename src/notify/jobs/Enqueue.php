<?php namespace nyx\notify\jobs;

// Internal dependencies
use nyx\notify\interfaces;

/**
 * Enqueue Job
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Enqueue implements \Illuminate\Contracts\Queue\ShouldQueue
{
    /**
     * The traits of a Notification Queue Handler instance.
     */
    use \Illuminate\Bus\Queueable, \Illuminate\Queue\SerializesModels;

    /**
     * @var mixed   The entities which shall receive the wrapped Notification.
     */
    protected $notifiables;

    /**
     * @var interfaces\Notification The Notification that shall be sent.
     */
    protected $notification;

    /**
     * Creates a new Enqueue Job instance.
     *
     * @param   mixed                   $notifiables    The entities which shall receive the wrapped Notification.
     * @param   interfaces\Notification $notification   The Notification that shall be sent.
     */
    public function __construct($notifiables, interfaces\Notification $notification)
    {
        $this->notifiables  = $notifiables;
        $this->notification = $notification;
    }

    /**
     * Sends the wrapped Notification.
     *
     * @param   interfaces\Dispatcher   $dispatcher
     */
    public function handle(interfaces\Dispatcher $dispatcher)
    {
        $dispatcher->sendNow($this->notifiables, $this->notification);
    }
}
