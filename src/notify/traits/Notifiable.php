<?php namespace nyx\notify\traits;

// External dependencies
use nyx\utils\str;

// Internal dependencies
use nyx\notify\interfaces;

/**
 * Notifiable Trait
 *
 * Allows for the implementation of the Notifiable Interface - @see \nyx\notify\interfaces\Notifiable.
 *
 * Important note: Currently relies on Laravel's app() helper method being available and this dependency is not covered
 * by Composer's dependencies. The trait itself is not used anywhere in the Notify component but if you decide to use
 * it in your own Notifiable, the assumption (currently, subject to change) is that it is in a typical, full Laravel
 * installation (@todo).
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
trait Notifiable
{
    /**
     * @see \nyx\notify\interfaces\Notifiable::notify()
     */
    public function notify(interfaces\Notification $notification)
    {
        app(interfaces\Dispatcher::class)->send([$this], $notification);
    }

    /**
     * @see \nyx\notify\interfaces\Notifiable::routeNotification()
     *
     * Note: This is a catch-all method. You can either override it or provide getNotificationRouteFor* methods
     *       for each transport supported by the entity. For example, a method returning data for the Slack
     *       transport would be called: "routeNotificationForSlack()"
     *
     *       For any transport that is not supported, or if you simply want to circumvent notifying an entity
     *       under certain conditions, return false and/or simply do not implement methods for those transports.
     */
    public function routeNotification(string $transport, $message)
    {
        if (method_exists($this, $method = 'routeNotificationFor'.str\Cases::studly($transport))) {
            return $this->{$method}($message);
        }

        return false;
    }
}
