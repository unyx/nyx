<?php namespace nyx\notify\transports\slack;

/**
 * Slack Response Message
 *
 * A Response is a special kind of Message that is sent after an Action of an Attachment has been invoked
 * inside Slack and Slack has called the designated callback URL or as a response to an invocation of
 * a registered Slack command.
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Response extends Message
{
    /**
     * The types a Response can be of.
     */
    const TYPE_CHANNEL   = 'in_channel';
    const TYPE_EPHEMERAL = 'ephemeral';

    /**
     * @var string  The type of the Response. One of the TYPE_* class constants.
     */
    protected $type;

    /**
     * @var string  The URL this Response should be sent to.
     */
    protected $url;

    /**
     * @var bool Whether the original Message should be replaced by this Response. When false, this Response
     *           will be considered a brand new Message.
     */
    protected $replaceOriginal;

    /**
     * @var bool Whether the original Message should be deleted. If a new one is sent along this Response,
     *           it will be published as a brand new Message.
     */
    protected $deleteOriginal;

    /**
     * {@inheritDoc}
     */
    public function setAttributes(array $attributes) : Message
    {
        parent::setAttributes($attributes);

        if (isset($attributes['response_type'])) {
            $this->setType($attributes['response_type']);
        }

        if (isset($attributes['response_url'])) {
            $this->setResponseUrl($attributes['response_url']);
        }

        if (isset($attributes['replace_original'])) {
            $this->setReplaceOriginal($attributes['replace_original']);
        }

        if (isset($attributes['delete_original'])) {
            $this->setDeleteOriginal($attributes['delete_original']);
        }

        return $this;
    }

    /**
     * Returns the type of the Response.
     *
     * @return  string
     */
    public function getType() : ?string
    {
        return $this->type;
    }

    /**
     * Sets the type of the Response.
     *
     * @param   string  $type
     * @return  $this
     * @throws  \InvalidArgumentException
     */
    public function setType(string $type) : Response
    {
        if ($type !== self::TYPE_CHANNEL && $type !== self::TYPE_EPHEMERAL) {
            throw new \InvalidArgumentException('Expected type to be one of ['.self::TYPE_CHANNEL.', '.self::TYPE_CHANNEL.'], got ['.$type.'] instead.');
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Returns the URL this Response should be sent to.
     *
     * @return  string
     */
    public function getResponseUrl() : ?string
    {
        return $this->url;
    }

    /**
     * Sets the URL this Response should be sent to.
     *
     * @param   string  $url
     * @return  $this
     */
    public function setResponseUrl(string $url) : Response
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Checks whether the original Message should be replaced by this Response.
     *
     * @return  bool
     */
    public function shouldReplaceOriginal() : bool
    {
        return $this->replaceOriginal === true;
    }

    /**
     * Sets whether the original Message should be replaced by this Response.
     *
     * @param   bool    $replace
     * @return  $this
     */
    public function setReplaceOriginal(bool $replace) : Response
    {
        $this->replaceOriginal = $replace;

        return $this;
    }

    /**
     * Checks whether the original Message should be deleted.
     *
     * @return  bool
     */
    public function shouldDeleteOriginal() : bool
    {
        return $this->deleteOriginal === true;
    }

    /**
     * Sets whether the original Message should be deleted.
     *
     * @param   bool    $delete
     * @return  $this
     */
    public function setDeleteOriginal(bool $delete) : Response
    {
        $this->deleteOriginal = $delete;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray() : array
    {
        return array_merge(parent::toArray(), [
            'response_type'    => $this->getType(),
            'response_url'     => $this->getResponseUrl(),
            'replace_original' => $this->shouldReplaceOriginal(),
            'delete_original'  => $this->shouldDeleteOriginal(),
        ]);
    }
}
