<?php namespace nyx\notify\interfaces;

/**
 * Notification Interface
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Notification
{
    /**
     * Returns a list of Transport names supported by this Notification for the given Notifiable.
     *
     * @param   mixed   $notifiable The Notifiable this Notification should be sent to.
     * @return  array
     */
    public function via($notifiable) : array;
}
