<?php namespace nyx\notify\transports\mail\drivers\traits;

/**
 * Counts Recipients Mail Driver Trait
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
trait CountsRecipients
{
    /**
     * Returns the number of recipients defined in the Message.
     *
     * @param   \Swift_Mime_Message $message    The Message whose recipients will be counted.
     * @return  int                             The number of recipients.
     */
    protected function countRecipients(\Swift_Mime_Message $message) : int
    {
        return count((array) $message->getTo() + (array) $message->getCc() + (array) $message->getBcc());
    }
}
