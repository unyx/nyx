<?php namespace nyx\notify\interfaces;

/**
 * Notifiable Interface
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Notifiable
{
    /**
     * Notifies the underlying entity with the given Notification.
     *
     * @param   Notification $notification
     */
    public function notify(Notification $notification);

    /**
     * Returns data necessary for the specified Transport to route a constructed notification message
     * to the underlying entity, granting the Notifiable the ability to modify the message before delivery.
     *
     * @param   string  $transport  The name of the Transport to return metadata for.
     * @param   object  $message    The notification message being routed.
     * @return  mixed
     */
    public function routeNotification(string $transport, $message);
}
