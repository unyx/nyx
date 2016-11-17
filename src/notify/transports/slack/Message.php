<?php namespace nyx\notify\transports\slack;

// External dependencies
use nyx\core;
use nyx\diagnostics;

/**
 * Slack Message
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 * @todo        PHP7.1 pass for nullable return types (missing ?string on most methods).
 */
class Message implements core\interfaces\Serializable
{
    /**
     * The parse modes that can be requested for a Message.
     */
    const PARSE_DEFAULT = null;
    const PARSE_FULL    = 'full';
    const PARSE_NONE    = 'none';

    /**
     * The traits of a Slack Message.
     */
    use core\traits\Serializable;

    /**
     * @var string  The endpoint for this Message. Note: Only has effect when using the Slack Webhook Transport, not
     *              the Web API.
     */
    protected $endpoint;

    /**
     * @var string  The text content of the Message.
     */
    protected $text;

    /**
     * @var string  The channel this Message shall be sent to.
     */
    protected $channel;

    /**
     * @var string  The name this Message shall be sent as.
     */
    protected $username;

    /**
     * @var string  The icon of the Message.
     */
    protected $icon;

    /**
     * @var message\Attachment[]    The Attachments of the Message.
     */
    protected $attachments = [];

    /**
     * @var string  The parse mode of this Message. One of the PARSE_* class constants.
     */
    protected $parse;

    /**
     * Creates a new Slack Message instance.
     *
     * @param   array   $attributes
     */
    public function __construct(array $attributes = null)
    {
        if (!empty($attributes)) {
            $this->setAttributes($attributes);
        }
    }

    /**
     * Sets the attributes of this Message.
     *
     * @param   array   $attributes
     * @return  $this
     */
    public function setAttributes(array $attributes) : Message
    {
        if (isset($attributes['endpoint'])) {
            $this->setEndpoint($attributes['endpoint']);
        }

        if (isset($attributes['text'])) {
            $this->setText($attributes['text']);
        }

        if (isset($attributes['channel'])) {
            $this->setChannel($attributes['channel']);
        }

        if (isset($attributes['username'])) {
            $this->setUsername($attributes['username']);
        }

        if (isset($attributes['icon'])) {
            $this->setIcon($attributes['icon']);
        }

        foreach($attributes['attachments'] as $attachment) {
            $this->attach($attachment);
        }

        if (isset($attributes['parse'])) {
            $this->setParseMode($attributes['parse']);
        }

        return $this;
    }

    /**
     * Returns the endpoint for this Message.
     *
     * Note: Only has effect when using the Slack Webhook Transport, not the Web API.
     *
     * @return  string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Sets the endpoint for this Message.
     *
     * Note: Only has effect when using the Slack Webhook Transport, not the Web API.
     *
     * @param   string  $url
     * @return  $this
     */
    public function setEndpoint(string $url) : Message
    {
        $this->endpoint = $url;

        return $this;
    }

    /**
     * Returns the text content of the Message.
     *
     * @return  string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Sets the text content of the Message.
     *
     * @param   string  $text
     * @return  $this
     */
    public function setText(string $text) : Message
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Returns the channel this Message shall be sent to.
     *
     * @return  string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Sets the channel this Message shall be sent to.
     *
     * @param   string  $channel
     * @return  $this
     */
    public function setChannel(string $channel = null) : Message
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Returns the name this Message shall be sent as.
     *
     * @return  string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Sets the name this Message shall be sent as.
     *
     * @param   string  $name
     * @return  $this
     */
    public function setUsername(string $name = null) : Message
    {
        $this->username = $name;

        return $this;
    }

    /**
     * Returns the icon of the Message.
     *
     * @return  string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Sets the icon of the Message.
     *
     * @param   string  $icon
     * @return  $this
     */
    public function setIcon(string $icon = null) : Message
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Adds an Attachment to the Message.
     *
     * @param   callable|array|message\Attachment   $attachment When a callable is given, an Attachment will be created
     *                                                          and passed to the callable. With an array, an Attachment
     *                                                          will be constructed based on the array. Otherwise
     *                                                          an existing instance of an Attachment can be passed in.
     * @return  $this
     * @throws  \InvalidArgumentException                       When $attachment is neither of the supported types.
     */
    public function attach($attachment) : Message
    {
        // Let's try to resolve the input into something usable.
        if (!($attachment = $this->resolveAttachment($attachment)) instanceof message\Attachment) {
            throw new \InvalidArgumentException("Expected a callable, an array or an instance of ".message\Attachment::class.", got [".diagnostics\Debug::getTypeName($attachment)."] instead.");
        }

        $this->attachments[] = $attachment;

        return $this;
    }

    /**
     * Checks whether an Attachment with the specified $index is set.
     *
     * @return  bool
     */
    public function hasAttachment(int $index) : bool
    {
        return isset($this->attachments[$index]);
    }

    /**
     * Returns the Attachment at the given $index.
     *
     * @return  message\Attachment
     * @throws  \OutOfBoundsException
     */
    public function getAttachment(int $index) : message\Attachment
    {
        if (!isset($this->attachments[$index])) {
            throw new \OutOfBoundsException("No Attachment at the specified index [$index].");
        }

        return $this->attachments[$index];
    }

    /**
     * Sets the Attachment at the given $index. Overwrites, if applicable.
     *
     * @see     attach()
     *
     * @param   callable|array|message\Attachment   $attachment
     * @return  $this
     * @throws  \InvalidArgumentException
     */
    public function setAttachment(int $index, $attachment) : Message
    {
        // Let's try to resolve the input into something usable.
        if (!($attachment = $this->resolveAttachment($attachment)) instanceof message\Attachment) {
            throw new \InvalidArgumentException("Expected a callable, an array or an instance of ".message\Attachment::class.", got [".diagnostics\Debug::getTypeName($attachment)."] instead.");
        }

        $this->attachments[$index] = $attachment;

        return $this;
    }

    /**
     * Returns the Attachments of the Message.
     *
     * @return  message\Attachment[]
     */
    public function getAttachments() : array
    {
        return $this->attachments;
    }

    /**
     * Sets the Attachments of the Message.
     *
     * @param   message\Attachment[]    $attachments
     * @return  $this
     */
    public function setAttachments(array $attachments) : Message
    {
        $this->attachments = [];

        foreach ($attachments as $attachment) {
            $this->attach($attachment);
        }

        return $this;
    }

    /**
     * Returns the number of Attachments in this Message.
     *
     * @return  int
     */
    public function countAttachments() : int
    {
        return count($this->attachments);
    }

    /**
     * Returns the parse mode of this Message.
     *
     * @return  string
     */
    public function getParseMode()
    {
        return $this->parse;
    }

    /**
     * Sets the parse mode of this Message.
     *
     * @param   string  $mode               One of the PARSE_* class constants.
     * @return  $this
     * @throws  \InvalidArgumentException   When passing a $mode which is not one of the PARSE_* class constants.
     */
    public function setParseMode(string $mode = self::PARSE_DEFAULT) : Message
    {
        if (!in_array($mode, [self::PARSE_DEFAULT, self::PARSE_FULL, self::PARSE_NONE])) {
            throw new \InvalidArgumentException("Unknown parse mode requested [$mode].");
        }

        $this->parse = $mode;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($data)
    {
        $this->setAttributes(unserialize($data));
    }

    /**
     * {@inheritDoc}
     */
    public function toArray() : array
    {
        $data = [
            'endpoint'    => $this->getEndpoint(),
            'text'        => $this->getText(),
            'channel'     => $this->getChannel(),
            'username'    => $this->getUsername(),
            'icon'        => $this->getIcon(),
            'parse'       => $this->getParseMode(),
            'attachments' => [],
            'link_names'  => 1
        ];

        foreach ($this->getAttachments() as $attachment) {
            $data['attachments'][] = $attachment->toArray();
        }

        return $data;
    }

    /**
     * Attempts to create a slack\Attachment instance based on the $attachment data given.
     *
     * @param   mixed   $attachment
     * @return  mixed                   Either an instantiated slack\Attachment, or a passthrough of the input data.
     */
    protected function resolveAttachment($attachment)
    {
        if (is_callable($attachment)) {
            $callable   = $attachment;
            $attachment = new message\Attachment;

            call_user_func($callable, $attachment);

            return $attachment;
        }

        if (is_array($attachment)) {
            return new message\Attachment($attachment);
        }

        return $attachment;
    }
}
