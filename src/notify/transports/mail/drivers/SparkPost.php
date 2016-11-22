<?php namespace nyx\notify\transports\mail\drivers;

// Internal dependencies
use nyx\notify\transports\mail;

/**
 * SparkPost Mail Driver
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class SparkPost implements mail\interfaces\Driver
{
    /**
     * The traits of a SparkPost Mail Driver instance.
     */
    use traits\ExposesBcc;

    /**
     * @var string  The API key used to authorize requests to SparkPost's API.
     */
    protected $key;

    /**
     * @var array   An array of additional options that shall be passed along with each API request.
     */
    protected $options;

    /**
     * @var \GuzzleHttp\ClientInterface The underlying HTTP Client instance.
     */
    protected $client;

    /**
     * Creates a new SparkPost Mail Driver instance.
     *
     * @param   \GuzzleHttp\ClientInterface $client     The HTTP Client to use.
     * @param   string                      $key        The API key to be used to authorize requests to SparkPost's API.
     * @param   array                       $options    An array of additional options that shall be passed along with
     *                                                  each API request.
     */
    public function __construct(\GuzzleHttp\ClientInterface $client, string $key, array $options = [])
    {
        $this->key     = $key;
        $this->options = $options;
        $this->client  = $client;
    }

    /**
     * {@inheritDoc}
     */
    public function send(\Swift_Mime_Message $message, &$failures = null)
    {
        $recipients = $this->getRecipients($message);

        // Unset the BCC recipients temporarily since we don't want to pass them along with the MIME.
        $this->hideBcc($message);

        try {
            $this->client->request('POST', 'https://api.sparkpost.com/api/v1/transmissions', [
                'headers' => [
                    'Authorization' => $this->key,
                ],
                'json' => [
                    'recipients' => $recipients,
                    'options'    => $this->options ?: ['start_time' => 'now'],
                    'content'    => [
                        'email_rfc822' => $message->toString(),
                    ]
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
     * SparkPost's "transmissions" endpoint for the "recipients" field.
     *
     * @param   \Swift_Mime_Message $message
     * @return  array
     */
    protected function getRecipients(\Swift_Mime_Message $message) : array
    {
        $recipients = (array) $message->getTo() + (array) $message->getCc() + (array) $message->getBcc();
        $result     = [];

        foreach ($recipients as $address => $name) {
            $result[] = [
                'address' => $name
                    ? ['email' => $address, 'name'  => $name]
                    : $address
            ];
        }

        return $result;
    }
}
