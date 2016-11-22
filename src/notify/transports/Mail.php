<?php namespace nyx\notify\transports;

// External dependencies
use nyx\utils;

// Internal dependencies
use nyx\notify\interfaces;

/**
 * Mail Transport
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Mail implements interfaces\Transport
{
    /**
     * @var mail\interfaces\Mailer  The Mailer in use for sending out Messages.
     */
    protected $mailer;

    /**
     * Creates a new Mail Transport instance.
     *
     * @param   mail\interfaces\Mailer  $mailer The Mailer to use for sending out Messages.
     */
    public function __construct(mail\interfaces\Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * {@inheritDoc}
     */
    public function send(interfaces\Notifiable $notifiable, interfaces\Notification $notification)
    {
        /* @var mail\interfaces\Mailable $notification */
        if (!$this->supports($notification)) {
            throw new \InvalidArgumentException('The given Notification is not supported (did you forget to implement the Mailable Interface?).');
        }

        if (false === $notifiable->routeNotification('mail', $message = $notification->toMail($notifiable))) {
            return;
        }

        // The Notification might have built a subject during the toMail() call, but that's optional.
        // If no subject is available, we are going to use the humanized name of the Notification's class.
        if (empty($message->getSubject())) {
            $message->setSubject(utils\str\Cases::title(utils\str\Cases::delimit(class_basename($notification), ' ')));
        }

        // And finally - just send the message.
        $this->mailer->send($message);
    }

    /**
     * {@inheritDoc}
     */
    public function supports(interfaces\Notification $notification) : bool
    {
        return ($notification instanceof mail\interfaces\Mailable);
    }
}
