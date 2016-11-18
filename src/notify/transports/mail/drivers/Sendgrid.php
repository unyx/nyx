<?php namespace nyx\notify\transports\mail\drivers;

// Internal dependencies
use nyx\notify\transports\mail;

/**
 * Sendgrid Mail Driver
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 * @todo        Check whether the X-SMTPAPI header is automatically handled by the Web API and if not, parse its
 *              contents out manually and merge them with the payload.
 */
class Sendgrid implements mail\interfaces\Driver
{
    /**
     * The traits of a Sendgrid Mail Driver instance.
     */
    use traits\CountsRecipients;

    /**
     * @var string  The API key (bearer token) used to authorize requests to Sendgrid's API.
     */
    protected $key;

    /**
     * @var \GuzzleHttp\ClientInterface The underlying HTTP Client instance.
     */
    protected $client;

    /**
     * Creates a new Sendgrid Mail Driver instance.
     *
     * @param   \GuzzleHttp\ClientInterface $client     The HTTP Client to use.
     * @param   string                      $key        The API key to be used to authorize requests to SparkPost's API.
     */
    public function __construct(\GuzzleHttp\ClientInterface $client, string $key)
    {
        $this->key    = $key;
        $this->client = $client;
    }

    /**
     * {@inheritDoc}
     */
    public function send(\Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $this->client->request('POST', 'https://api.sendgrid.com/v3/mail/send', [
            'headers' => [
                'Authorization' => 'Bearer '.$this->key
            ],
            'json' => $this->messageToPayload($message),
        ]);

        return $this->countRecipients($message);
    }

    /**
     * Converts a MIME Message to an array payload in a structure understood by Sendgrid's "mail/send" endpoint.
     *
     * @param   \Swift_Mime_Message $message    The Message to convert.
     * @return  array                           The resulting payload.
     */
    protected function messageToPayload(\Swift_Mime_Message $message) : array
    {
        $payload = [
            'Subject' => $message->getSubject()
        ];

        $this->processHeaders($message, $payload);
        $this->processAddresses($message, $payload);
        $this->processMimeEntities($message, $payload);
        $this->processAttachments($message, $payload);

        return $payload;
    }

    /**
     * Processes the MIME headers of a MIME Message into the payload structure passed in by reference.
     *
     * @param   \Swift_Mime_Message $message    The MIME Message to process.
     * @param   array&              $payload    A reference to the payload structure.
     */
    protected function processHeaders(\Swift_Mime_Message $message, array &$payload)
    {
        if (!$headers = $message->getHeaders()->getAll()) {
            return;
        }

        $payload['Headers'] = [];

        /** @var \Swift_Mime_Header $header */
        foreach ($headers as $header) {

            // Omit headers which are handled elsewhere.
            if (in_array($fieldName = $header->getFieldName(), ['Subject', 'Content-Type', 'MIME-Version', 'Date', 'From', 'To'])) {
                continue;
            }

            if ($header instanceof \Swift_Mime_Headers_UnstructuredHeader || $header instanceof \Swift_Mime_Headers_OpenDKIMHeader) {
                $payload['Headers'][] = [
                    "Name"  => $fieldName,
                    "Value" => $header->getValue()
                ];

                continue;
            }

            // All other headers are handled in the same fashion.
            $payload['Headers'][] = [
                "Name"  => $fieldName,
                "Value" => $header->getFieldBody()
            ];
        }
    }

    /**
     * Processes the fields containing e-mail addresses (from, to, cc, etc.) in the MIME Message into
     * the payload structure passed in by reference.
     *
     * @param   \Swift_Mime_Message $message    The MIME Message to process.
     * @param   array&              $payload    A reference to the payload structure.
     */
    protected function processAddresses(\Swift_Mime_Message $message, array &$payload)
    {
        // Sendgrid expect the 'from' field to be a single entry at most, according to the docs.
        $payload['from'] = $this->processFirstAddress($message->getFrom());

        if (!isset($payload['personalizations'])) {
            $payload['personalizations'] = [];
        }

        $payload['personalizations']['to'] = $this->processAllAddresses($message->getTo());

        if ($cc = $message->getCc()) {
            $payload['personalizations']['cc'] = $this->processAllAddresses($cc);
        }

        if ($bcc = $message->getBcc()) {
            $payload['personalizations']['bcc'] = $this->processAllAddresses($bcc);
        }

        if ($replyTo = $message->getReplyTo()) {
            $payload['reply_to'] = $this->processFirstAddress($replyTo);
        }
    }

    /**
     * Parses the first and only the first address from the given array of e-mail addresses into a structure
     * understood by Sendgrid's API.
     *
     * @param   array   $addresses  The e-mail addresses to parse.
     * @return  array
     */
    protected function processFirstAddress(array $addresses) : array
    {
        foreach ($addresses as $email => $name) {
            return $name ? [
                'email' => $email,
                'name'  => $name
            ] : [
                'email' => $email
            ];
        }

        return [];
    }

    /**
     * Parses the given array of e-mail addresses into a structure understood by Sendgrid's API.
     *
     * @param   array   $addresses  The e-mail addresses to parse.
     * @return  array
     */
    protected function processAllAddresses(array $addresses) : array
    {
        $result = [];

        foreach ($addresses as $email => $name) {
            $result[] = $name ? [
                'email' => $email,
                'name'  => $name
            ] : [
                'email' => $email
            ];
        }

        return $result;
    }

    /**
     * Processes the MIME entities in the MIME Message into the payload structure passed in by reference.
     *
     * @param   \Swift_Mime_Message $message    The MIME Message to process.
     * @param   array&              $payload    A reference to the payload structure.
     */
    protected function processMimeEntities(\Swift_Mime_Message $message, array &$payload)
    {
        switch ($message->getContentType()) {
            case 'text/html':
            case 'multipart/alternative':
            case 'multipart/mixed':

                // text/plain, if present, must be the first key according to the docs.
                if ($plain = $this->getMimePart($message, 'text/plain')) {
                    $payload['content'][] = [
                        'type'  => 'text/plain',
                        'value' => $plain->getBody()
                    ];
                }

                $payload['content'][] = [
                    'type'  => 'text/html',
                    'value' => $message->getBody()
                ];

                break;

            default:

                $payload['content'][] = [
                    'type'  => 'text/plain',
                    'value' => $message->getBody()
                ];

                if ($html = $this->getMimePart($message, 'text/html')) {
                    $payload['content'][] = [
                        'type'  => 'text/html',
                        'value' => $html->getBody()
                    ];
                }
        }
    }

    /**
     * Returns the MIME part of the specified content type contained in the given MIME message, if it's present.
     *
     * @param   \Swift_Mime_Message     $message    The MIME Message to process.
     * @param   string                  $mimeType   The content type the part to return must be of.
     * @return  \Swift_Mime_MimeEntity
     */
    protected function getMimePart(\Swift_Mime_Message $message, string $mimeType)
    {
        foreach ($message->getChildren() as $part) {
            if (0 === strpos($part->getContentType(), $mimeType) && !$part instanceof \Swift_Mime_Attachment) {
                return $part;
            }
        }
    }

    /**
     * Processes the attachments in the MIME Message into the payload structure passed in by reference.
     *
     * @param   \Swift_Mime_Message $message    The MIME Message to process.
     * @param   array&              $payload    A reference to the payload structure.
     */
    protected function processAttachments(\Swift_Mime_Message $message, array &$payload)
    {
        if (!$children = $message->getChildren()) {
            return;
        }

        $payload['attachments'] = [];

        foreach ($children as $attachment) {

            // Omit all MIME Entities that aren't attachments.
            if (!$attachment instanceof \Swift_Mime_Attachment) {
                continue;
            }

            $data = [
                'filename'    => $attachment->getFilename(),
                'content'     => base64_encode($attachment->getBody()),
                'type'        => $attachment->getContentType(),
                'disposition' => $attachment->getDisposition(),
            ];

            if ($attachment->getDisposition() !== 'attachment' && null !== $cid = $attachment->getId()) {
                $data['content_id'] = 'cid:'.$cid;
            }

            $payload['attachments'][] = $data;
        }
    }
}
