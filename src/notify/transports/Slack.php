<?php namespace nyx\notify\transports;

// Internal dependencies
use nyx\notify\interfaces;

/**
 * Slack Transport
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Slack implements interfaces\Transport
{
    /**
     * The types Message icons can be of.
     */
    const ICON_TYPE_URL   = 'icon_url';
    const ICON_TYPE_EMOJI = 'icon_emoji';

    /**
     * @var \GuzzleHttp\ClientInterface     The underlying HTTP Client instance.
     */
    protected $client;

    /**
     * @var string  The oAuth token to authorize API requests with (when not using webhook endpoints).
     */
    protected $token;

    /**
     * @var string  The (Webhook) endpoint to send messages to (when not using the Web API).
     */
    protected $endpoint;

    /**
     * @var string  The default username to send messages as.
     */
    protected $username;

    /**
     * @var string  The default icon to send messages with.
     */
    protected $icon;

    /**
     * @var string  The default parse mode of Messages. One of the slack\Message::PARSE_* class constants.
     */
    protected $parse;

    /**
     * @var bool    Whether names (like @someone) should be linked or left raw by Slack.
     */
    protected $linkNames;

    /**
     * @var bool    Whether Slack should unfurl text-based URLs.
     */
    protected $unfurlLinks;

    /**
     * @var bool    Whether Slack should unfurl media URLs.
     */
    protected $unfurlMedia;

    /**
     * @var bool    Whether the text of the messages sent should be parsed as Slack's markdown flavour or treated as
     *              raw text.
     */
    protected $allowMarkdown;

    /**
     * @var array   The attachment fields that should be parsed by Slack's markdown flavour.
     */
    protected $markdownInAttachments;

    /**
     * Parses an icon "definition" and determines whether it should be treated as an URL or a Slack-recognized
     * emoji.
     *
     * @param   string  $icon   The icon's "definition".
     * @return  string          One of the ICON_TYPE_* class constants.
     */
    public static function determineIconType(string $icon) : string
    {
        // Filter_var() will do the trick since we're not in a security-sensitive context.
        if (filter_var($icon, FILTER_VALIDATE_URL)) {
            return self::ICON_TYPE_URL;
        }

        return self::ICON_TYPE_EMOJI;
    }

    /**
     * Constructs a new Slack Transport instance.
     *
     * @param   array                       $config     The Transport's configuration.
     * @param   \GuzzleHttp\ClientInterface $client     A Guzzle HTTP Client instance.
     * @todo                                            Proper parsing of config options and error-recovery.
     */
    public function __construct(array $config, \GuzzleHttp\ClientInterface $client)
    {
        $this->token                 = $config['token']                   ?? null;
        $this->endpoint              = $config['endpoint']                ?? null;
        $this->username              = $config['username']                ?? null;
        $this->icon                  = $config['icon']                    ?? null;
        $this->parse                 = $config['parse']                   ?? slack\Message::PARSE_DEFAULT;
        $this->linkNames             = $config['link_names']              ?? true;
        $this->unfurlLinks           = $config['unfurl_links']            ?? false;
        $this->unfurlMedia           = $config['unfurl_media']            ?? true;
        $this->allowMarkdown         = $config['allow_markdown']          ?? true;
        $this->markdownInAttachments = $config['markdown_in_attachments'] ?? [];

        $this->client = $client;
    }

    /**
     * {@inheritDoc}
     *
     * @throws  \InvalidArgumentException   When the Notification casts down to a Message without text nor attachments.
     */
    public function send(interfaces\Notifiable $notifiable, interfaces\Notification $notification)
    {
        /* @var slack\interfaces\Slackable $notification */
        if (!$this->supports($notification)) {
            throw new \InvalidArgumentException('The given Notification is not supported (did you forget to implement the Slackable Interface?).');
        }

        if (false === $notifiable->routeNotification('slack', $message = $notification->toSlack($notifiable))) {
            return;
        }

        // Note: The dual 'to()' cast is intended - toSlack() above will let the Notification build the appropriate
        // Message while the latter toArray() call flattens the whole structure down into an array that we can more
        // easily digest and pass on to Slack itself.
        $message = $message->toArray();

        // We need text or an attachment for the message to actually be displayed in Slack.
        if (empty($message['text']) && empty($message['attachments'])) {
            throw new \RuntimeException('A message to Slack must contain at least either text or an attachment, got neither.');
        }

        // Apply our defaults where the Message doesn't override them.
        $message['token']        = $message['token']    ?? $this->token;
        $message['endpoint']     = $message['endpoint'] ?? $this->endpoint;
        $message['username']     = $message['username'] ?? $this->username;
        $message['parse']        = $message['parse']    ?? $this->parse;
        $message['link_names']   = $this->linkNames     ? 1 : 0;
        $message['unfurl_links'] = $this->unfurlLinks;
        $message['unfurl_media'] = $this->unfurlMedia;
        $message['mrkdwn']       = $this->allowMarkdown;
        $message['mrkdwn_in']    = $this->markdownInAttachments;

        // We're applying the icon separately since we need to know what key it's going to be sent as.
        $icon = $message['icon'] ?? $this->icon;

        if ($icon) {
            $message[static::determineIconType($icon)] = $icon;
        }

        if (isset($message['response_url'])) {
            $this->sendResponse($message);
        } elseif ($message['token']) {
            $this->sendApiMessage($message);
        } elseif ($message['endpoint']) {
            $this->sendWebhookMessage($message);
        } else {
            throw new \InvalidArgumentException('No oAuth token nor webhook endpoint given, could not send the Notification.');
        }
    }

    /**
     * Performs the actual sending of a Message to a Slack Webhook endpoint.
     *
     * @param   array   $message    The Message's data (toArray()'ed).
     */
    protected function sendWebhookMessage(array $message)
    {
        $this->client->request('POST', $message['endpoint'], [
            'json' => $message,
        ]);
    }

    /**
     * Performs the actual sending of a Message to Slack's Web API.
     *
     * @param   array   $message    The Message's data (toArray()'ed).
     */
    protected function sendApiMessage(array $message)
    {
        // Slack rejects PHP-style arrays so we need to encode them as JSON if they're present.
        if (!empty($message['mrkdwn_in'])) {
            $message['mrkdwn_in'] = json_encode($message['mrkdwn_in']);
        }

        if (!empty($message['attachments'])) {
            $message['attachments'] = json_encode($message['attachments']);
        }

        $this->client->request('POST', 'https://slack.com/api/chat.postMessage', [
            'form_params' => $message,
        ]);
    }

    /**
     * Performs the actual sending of a Response to its Response URL provided by Slack.
     *
     * @param   array   $message    The Message's data (toArray()'ed).
     */
    protected function sendResponse(array $message)
    {
        $this->client->request('POST', $message['response_url'], [
            'json' => $message,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function supports(interfaces\Notification $notification) : bool
    {
        return ($notification instanceof slack\interfaces\Slackable);
    }
}
