<?php namespace nyx\notify\transports\mail\drivers;

// Internal dependencies
use nyx\notify\transports\mail;

/**
 * Postmark Mail Driver
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Postmark implements mail\interfaces\Driver
{
    /**
     * The traits of a Postmark Mail Driver instance.
     */
    use traits\CountsRecipients;

    /**
     * @var string  The API key used to authorize requests to Postmark's API.
     */
    protected $key;

    /**
     * @var \GuzzleHttp\ClientInterface The underlying HTTP Client instance.
     */
    protected $client;

    /**
     * Creates a new Postmark Mail Driver instance.
     *
     * @param   \GuzzleHttp\ClientInterface $client     The HTTP Client to use.
     * @param   string                      $key        The API key to be used to authorize requests to Postmark's API.
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
        $this->client->request('POST','https://api.postmarkapp.com/email', [
            'headers' => [
                'X-Postmark-Server-Token' => $this->key
            ],
            'json' => $this->messageToPayload($message),
        ]);

        return $this->countRecipients($message);
    }

    /**
     * Converts a MIME Message to an array payload in a structure understood by Postmark's "email" endpoint.
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

                // Special treatment for the 'X-PM-Tag' header if it's available, which we'll pass into the payload
                // directly.
                if ($fieldName === 'X-PM-Tag'){
                    $payload["Tag"] = $header->getValue();
                } else {
                    $payload['Headers'][] = [
                        "Name"  => $fieldName,
                        "Value" => $header->getValue()
                    ];
                }

                continue;
            }

            // All other headers are handled in the same fashion.
            $payload['Headers'][] = [
                "Name"  => $fieldName,
                "Value" => $header->getFieldBody()
            ];

            // @see http://developer.postmarkapp.com/developer-send-smtp.html
            if ($fieldName === 'Message-ID') {
                $payload['Headers'][] = [
                    "Name"  => 'X-PM-KeepID',
                    "Value" => 'true'
                ];
            }
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
        $payload['From'] = $this->emailsToString($message->getFrom());
        $payload['To']   = $this->emailsToString($message->getTo());

        if ($cc = $message->getCc()) {
            $payload['Cc'] = $this->emailsToString($cc);
        }

        if ($bcc = $message->getBcc()) {
            $payload['Bcc'] = $this->emailsToString($bcc);
        }

        if ($replyTo = $message->getReplyTo()) {
            $payload['ReplyTo'] = $this->emailsToString($replyTo);
        }
    }

    /**
     * Converts an array of e-mail addresses into a string compliant with Postmark's API's format.
     *
     * @param   array   $emails     The e-mail addresses to convert.
     * @return  string
     */
    protected function emailsToString(array $emails) : string
    {
        $addresses = [];

        foreach ($emails as $email => $name) {
            $addresses[] = $name ? '"' . str_replace('"', '\\"', $name) . "\" <{$email}>" : $email;
        }

        return implode(',', $addresses);
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
                $payload['HtmlBody'] = $message->getBody();

                if ($plain = $this->getMimePart($message, 'text/plain')) {
                    $payload['TextBody'] = $plain->getBody();
                }

                break;

            default:
                $payload['TextBody'] = $message->getBody();

                if ($html = $this->getMimePart($message, 'text/html')) {
                    $payload['HtmlBody'] = $html->getBody();
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

        $payload['Attachments'] = [];

        foreach ($children as $attachment) {

            // Omit all entities that aren't actually attachments.
            if (!$attachment instanceof \Swift_Mime_Attachment) {
                continue;
            }

            $data = [
                'Name'        => $attachment->getFilename(),
                'Content'     => base64_encode($attachment->getBody()),
                'ContentType' => $attachment->getContentType()
            ];

            if ($attachment->getDisposition() !== 'attachment' && null !== $attachment->getId()) {
                $data['ContentID'] = 'cid:'.$attachment->getId();
            }

            $payload['Attachments'][] = $data;
        }
    }
}
