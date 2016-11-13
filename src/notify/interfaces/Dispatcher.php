<?php namespace nyx\notify\interfaces;

/**
 * Notification Dispatcher Interface
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Dispatcher
{
    /**
     * Sends the given Notification to the given entities.
     *
     * @param   mixed           $notifiables
     * @param   Notification    $notification
     */
    public function send($notifiables, Notification $notification);

    /**
     * Sends the given Notification synchronously to the given entities.
     *
     * @param   mixed           $notifiables
     * @param   Notification    $notification
     */
    public function sendNow($notifiables, Notification $notification);
}
