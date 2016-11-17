<?php namespace nyx\notify\interfaces;

/**
 * Notification Transport Interface
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Transport
{
    /**
     * Sends the given Notification to the given Notifiable via this Transport.
     *
     * @param   Notifiable                  $notifiable     The Notifiable to send the Notification to.
     * @param   Notification                $notification   The Notification to send.
     * @throws  \InvalidArgumentException                   When the Notification is not supported by this Transport.
     */
    public function send(Notifiable $notifiable, Notification $notification);

    /**
     * Checks whether the Transport supports the given Notification.
     *
     * @param   Notification    $notification
     * @return  bool
     */
    public function supports(Notification $notification) : bool;
}
