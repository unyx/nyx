<?php namespace nyx\notify;

// External dependencies
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Bus\Dispatcher;

/**
 * Notification Transport Manager
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 * @todo        Proper eventing (beforeSend, afterSend, failedSend etc.) hooked into Nyx's Events component.
 * @todo        Add support for core Collections and PHP 7.1 iterables (Notifiable entities).
 */
class TransportManager extends \Illuminate\Support\Manager implements interfaces\Dispatcher
{
    /**
     * {@inheritDoc}
     */
    public function send($notifiables, interfaces\Notification $notification)
    {
        // In here, as opposed to self::sendNow(), we respect the ShouldQueue interface
        // and push the Notification onto the queue if it asks us to.
        if ($notification instanceof ShouldQueue) {
            $this->enqueue($notifiables, $notification);
            return;
        }

        $this->sendNow($notifiables, $notification);
    }

    /**
     * {@inheritDoc}
     */
    public function sendNow($notifiables, interfaces\Notification $notification)
    {
        if (!is_array($notifiables)) {
            $notifiables = [$notifiables];
        }

        foreach ($notifiables as $notifiable) {

            // Iterate over all transports the Notification specifies for the current Notifiable,
            // then determine whether it shall be sent and send it.
            foreach ($notification->via($notifiable) as $transport) {

                $transport = $this->driver($transport);

                if (!$this->shouldSend($notifiable, $notification, $transport)) {
                    continue;
                }

                // All clear at this point - let's dispatch the Notification.
                $transport->send($notifiable, $notification);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultDriver() : string
    {
        return 'mail';
    }

    /**
     * Determines whether the Notification should be sent at all, given the context.
     *
     * @param   interfaces\Notifiable   $notifiable     The entity being notified.
     * @param   interfaces\Notification $notification   The Notification being sent.
     * @param   interfaces\Transport    $transport      The Transport the Notification should be sent over.
     * @return  bool                                    True when the Notification should be sent, false otherwise.
     * @todo    onBeforeSend event allowing listeners to prevent dispatching.
     */
    protected function shouldSend(interfaces\Notifiable $notifiable, interfaces\Notification $notification, interfaces\Transport $transport) : bool
    {
        if (!$transport->supports($notification)) {
            return false;
        }

        return true;
    }

    /**
     * Enqueues the given Notification.
     *
     * @param   mixed                   $notifiables    The entities which shall receive the Notification.
     * @param   interfaces\Notification $notification   The Notification to enqueue.
     */
    protected function enqueue($notifiables, interfaces\Notification $notification)
    {
        // @todo Laravel's ShouldQueue interface doesn't actually cover access to those properties
        // so we'll need a more robust solution later.
        $this->app->make(Dispatcher::class)->dispatch(
            (new jobs\Enqueue($notifiables, $notification))
                ->onConnection($notification->connection)
                ->onQueue($notification->queue)
                ->delay($notification->delay)
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function createDriver($driver)
    {
        try {
            return parent::createDriver($driver);
        } catch (\InvalidArgumentException $exception) {

            // Re-throw if the driver wasn't recognized and isn't a fully-qualified (and existing) class name.
            if (!class_exists($driver)) {
                throw $exception;
            }

            return $this->app->make($driver);
        }
    }

    /**
     * Creates a Mail Transport.
     *
     * @return  transports\Mail
     */
    protected function createMailDriver() : transports\Mail
    {
        return $this->app->make(transports\Mail::class);
    }

    /**
     * Creates a Slack Transport.
     *
     * @return  transports\Slack
     */
    protected function createSlackDriver() : transports\Slack
    {
        return new transports\Slack($this->app->make('config')->get('services.slack'), new \GuzzleHttp\Client);
    }
}
