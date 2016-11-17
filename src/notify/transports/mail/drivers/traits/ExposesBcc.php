<?php namespace nyx\notify\transports\mail\drivers\traits;

// Internal dependencies
use nyx\notify\transports\mail;

/**
 * Exposes BCC Mail Driver Trait
 *
 * Designates a Driver for a provider that does not by itself hide the BCC recipients of a MIME message
 * and requires those to be passed in separately from the MIME entity.
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
trait ExposesBcc
{
    /**
     * The BCC recipients that should not be exposed.
     */
    protected $bcc;

    /**
     * Removes the BCC recipients from the Message while storing a local in-memory copy that can be restored
     * after a mailing transactions.
     *
     * @param   \Swift_Mime_Message $message    The Message whose BCC recipients the driver should hide.
     * @return  $this
     */
    protected function hideBcc(\Swift_Mime_Message $message) : mail\interfaces\Driver
    {
        $this->bcc = $message->getBcc();
        $message->setBcc([]);

        return $this;
    }

    /**
     * Reapplies the local in-memory copy of a BCC recipient list onto the given Message.
     *
     * @param   \Swift_Mime_Message $message    The Message to which the BCC recipients list copy should be applied.
     * @return  $this
     */
    protected function restoreBcc(\Swift_Mime_Message $message) : mail\interfaces\Driver
    {
        $message->setBcc($this->bcc);
        $this->bcc = null;

        return $this;
    }
}
