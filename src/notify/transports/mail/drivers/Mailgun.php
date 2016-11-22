<?php namespace nyx\notify\transports\mail\drivers;

// Internal dependencies
use nyx\notify\transports\mail;

/**
 * Mailgun Mail Driver
 *
 * Mailgun specific functions (like tagging or campaigns) can be used by passing the respective X-MAILGUN-* headers
 * along with the message, as described here: https://documentation.mailgun.com/user_manual.html#sending-via-smtp
 *
 * Example:
 *  $headers = $message->getHeaders();
 *  $headers->addTextHeader('X-Mailgun-Variables', '{"msg_id": "nyx123", "my_campaign_id": 8}');
 *  $headers->addTextHeader('X-Mailgun-Tag', 'first.tag');
 *  $headers->addTextHeader('X-Mailgun-Tag', 'second.tag');
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Mailgun implements mail\interfaces\Driver
{
    /**
     * The traits of a Mailgun Mail Driver instance.
     */
    use traits\ExposesBcc;

    /**
     * @var string  The API key used to authorize requests to Mailgun's API.
     */
    protected $key;

    /**
     * @var string  The API endpoint used by the Driver.
     */
    protected $endpoint;

    /**
     * @var \GuzzleHttp\ClientInterface The underlying HTTP Client instance.
     */
    protected $client;

    /**
     * Creates a new Mailgun Mail Driver instance.
     *
     * @param   \GuzzleHttp\ClientInterface $client     The HTTP Client to use.
     * @param   string                      $key        The API key to be used to authorize requests to Mailgun's API.
     * @param   string                      $domain     The domain to use to send mails from (must be registered with Mailgun).
     */
    public function __construct(\GuzzleHttp\ClientInterface $client, string $key, string $domain)
    {
        $this->key      = $key;
        $this->endpoint = 'https://api.mailgun.net/v3/'.$domain.'/messages.mime';
        $this->client   = $client;
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
            $this->client->request('POST', $this->endpoint, [
                'auth' => [
                    'api', $this->key
                ],
                'multipart' => [
                    ['name' => 'to',      'contents' => implode(',', $recipients)],
                    ['name' => 'message', 'contents' => $message->toString(), 'filename' => 'message.mime'],
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
     * Mailgun's "messages.mime" endpoint for the "to" field.
     *
     * @param   \Swift_Mime_Message $message
     * @return  array
     */
    protected function getRecipients(\Swift_Mime_Message $message) : array
    {
        $recipients = (array) $message->getTo() + (array) $message->getCc() + (array) $message->getBcc();
        $result     = [];

        foreach ($recipients as $address => $name) {
            $result[] = $name ? $name." <{$address}>" : $address;
        }

        return $result;
    }
}
