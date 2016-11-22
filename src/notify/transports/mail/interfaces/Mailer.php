<?php namespace nyx\notify\transports\mail\interfaces;

// Internal dependencies
use nyx\notify\transports\mail;

/**
 * Mailer Interface
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Mailer
{
    /**
     * Creates and/or sends a Mail Message.
     *
     * @param   array|string|mail\Message   $view       An array specifying the views to use (html, text, raw),
     *                                                  a string specifying a single view (html) or a cooked Message instance.
     * @param   array                       $data       The data to pass in to the cooked Message's views.
     * @param   callable                    $builder    A callable that will receive the Message as its first parameter
     *                                                  before the Message gets sent, allowing to tweak it further.
     * @param   array                       &$failures  A reference to an array which will hold data about all requested
     *                                                  recipients sending to whom failed.
     * @return  int                                     The number of recipients the Message has been sent to.
     */
    public function send($view, array $data = [], callable $builder = null, array &$failures = null) : int;
}
