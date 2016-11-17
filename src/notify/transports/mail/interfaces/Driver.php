<?php namespace nyx\notify\transports\mail\interfaces;

/**
 * Mail Driver Interface
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Driver
{
    /**
     * Sends a Mail Message.
     *
     * @param   \Swift_Mime_Message $message            The Message to send.
     * @param   array               $failedRecipients   A reference to an array which will hold data about all requested
     *                                                  recipients sending to whom failed.
     */
    public function send(\Swift_Mime_Message $message, &$failedRecipients = null);
}
