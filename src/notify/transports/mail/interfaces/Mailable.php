<?php namespace nyx\notify\transports\mail\interfaces;

// Internal dependencies
use nyx\notify\transports\mail;

/**
 * Mailable Interface
 *
 * Represents an object that can be cast to a Mail Message.
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Mailable
{
    /**
     * Returns a mail\Message representation of the object.
     *
     * @param   mixed           $context
     * @return  mail\Message
     */
    public function toMail($context) : mail\Message;
}
