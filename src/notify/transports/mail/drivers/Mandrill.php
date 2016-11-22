<?php namespace nyx\notify\transports\mail\drivers;

// Internal dependencies
use nyx\notify\transports\mail;

/**
 * Mandrill Mail Driver
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Mandrill implements mail\interfaces\Driver
{
    /**
     * The traits of a Mandrill Mail Driver instance.
     */
    use traits\ExposesBcc;

    /**
     * @var string  The API key used to authorize requests to Mandrill's API.
     */
    protected $key;

    /**
     * @var \GuzzleHttp\ClientInterface The underlying HTTP Client instance.
     */
    protected $client;

    /**
     * Creates a new Mandrill Mail Driver instance.
     *
     * @param   \GuzzleHttp\ClientInterface $client     The HTTP Client to use.
     * @param   string                      $key        The API key to be used to authorize requests to Mandrill's API.
     */
    public function __construct(\GuzzleHttp\ClientInterface $client, string $key)
    {
        $this->key    = $key;
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function send(\Swift_Mime_Message $message, &$failures = null)
    {
        $recipients = $this->getRecipients($message);

        // Unset the BCC recipients temporarily since we don't want to pass them along with the MIME.
        $this->hideBcc($message);

        try {
            $this->client->request('POST', 'https://mandrillapp.com/api/1.0/messages/send-raw.json', [
                'json' => [
                    'async'       => true,
                    'key'         => $this->key,
                    'to'          => $recipients,
                    'raw_message' => $message->toString()
                ]
            ]);
        } finally {
            // Always restore the BCC recipients.
            $this->restoreBcc($message);
        }

        return count($recipients);
    }

    /**
     * Returns an array of all of the recipients of the message (to, cc and bcc) in a format understood by
     * Mandrill's "send-raw" endpoint for the "to" field.
     *
     * @param   \Swift_Mime_Message $message
     * @return  array
     */
    protected function getRecipients(\Swift_Mime_Message $message) : array
    {
        return array_keys((array) $message->getTo() + (array) $message->getCc() + (array) $message->getBcc());
    }
}
