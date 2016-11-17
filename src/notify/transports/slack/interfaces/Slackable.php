<?php namespace nyx\notify\transports\slack\interfaces;

// Internal dependencies
use nyx\notify\transports\slack;

/**
 * Slackable Interface
 *
 * Represents an object that can be cast to a Slack Message.
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Slackable
{
    /**
     * Returns a slack\Message representation of the object.
     *
     * @param   mixed           $context
     * @return  slack\Message
     */
    public function toSlack($context) : slack\Message;
}
