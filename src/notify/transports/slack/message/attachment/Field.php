<?php namespace nyx\notify\transports\slack\message\attachment;

// External dependencies
use nyx\core;

/**
 * Slack Attachment Field
 *
 * See {@see https://api.slack.com/docs/message-attachments} and
 * {@see https://api.slack.com/docs/message-buttons#attachment_fields} for Slack's documentation on using
 * Messages with Attachments.
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 * @todo        PHP7.1 pass for nullable return types (missing ?string on most methods).
 */
class Field implements core\interfaces\Serializable
{
    /**
     * The traits of a Field.
     */
    use core\traits\Serializable;

    /**
     * @var string  Required. The title of the Field.
     */
    protected $title;

    /**
     * @var string  Required. The value of the Field.
     */
    protected $value;

    /**
     * @var bool    Whether the value of the Field is short enough to fit side by side other Fields.
     */
    protected $isShort = true;

    /**
     * Creates a new Field instance.
     *
     * @param   array   $attributes
     */
    public function __construct(array $attributes)
    {
        if (!empty($attributes)) {
            $this->setAttributes($attributes);
        }
    }

    /**
     * Sets the attributes of this Field.
     *
     * @param   array   $attributes
     * @return  $this
     */
    public function setAttributes(array $attributes) : Field
    {
        if (isset($attributes['title'])) {
            $this->setTitle($attributes['title']);
        }

        if (isset($attributes['value'])) {
            $this->setValue($attributes['value']);
        }

        if (isset($attributes['short'])) {
            $this->setIsShort($attributes['short']);
        }

        return $this;
    }

    /**
     * Returns the title of the Field.
     *
     * @return  string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title of the Field.
     *
     * @param   string  $title
     * @return  $this
     */
    public function setTitle(string $title) : Field
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Returns the value of the Field.
     *
     * @return  string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the value of the Field.
     *
     * @param   string  $value
     * @return  $this
     */
    public function setValue(string $value) : Field
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Returns whether the value of the Field is short enough to fit side by side other Fields.
     *
     * @return  bool
     */
    public function isShort() : bool
    {
        return $this->isShort === true;
    }

    /**
     * Sets whether the value of the Field is short enough to fit side by side other Fields.
     *
     * @param   string  $value
     * @return  $this
     */
    public function setIsShort(bool $value) : Field
    {
        $this->isShort = $value;

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
        return [
            'title' => $this->getTitle(),
            'value' => $this->getValue(),
            'short' => $this->isShort(),
        ];
    }
}
