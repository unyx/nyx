<?php namespace nyx\notify\transports\slack\message\attachment\actions;

// External dependencies
use nyx\core;

/**
 * Slack Message Confirmation Action
 *
 * See {@see https://api.slack.com/docs/message-buttons#confirmation_fields} for Slack's documentation on
 * using Messages with Confirmation Actions.
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 * @todo        PHP7.1 pass for nullable return types (missing ?string on most methods).
 */
class Confirmation implements core\interfaces\Serializable
{
    /**
     * The traits of a Confirmation Action.
     */
    use core\traits\Serializable;

    /**
     * @var string  The title of the pop up window. Optional.
     */
    protected $title;

    /**
     * @var string  The description and context of the action about to be performed or cancelled. Required.
     */
    protected $text;

    /**
     * @var string  The text label for the button to continue with an action. Optional, defaults to "Okay".
     */
    protected $okText;

    /**
     * @var string  The text label for the button to cancel the action. Optional, defaults to "Cancel".
     */
    protected $dismissText;

    /**
     * Creates a new Confirmation instance.
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
     * Sets the attributes of this Confirmation.
     *
     * @param   array   $attributes
     * @return  $this
     */
    public function setAttributes(array $attributes) : Confirmation
    {
        if (isset($attributes['title'])) {
            $this->setTitle($attributes['title']);
        }

        if (isset($attributes['text'])) {
            $this->setText($attributes['text']);
        }

        if (isset($attributes['ok_text'])) {
            $this->setOkText($attributes['ok_text']);
        }

        if (isset($attributes['dismiss_text'])) {
            $this->setDismissText($attributes['dismiss_text']);
        }

        return $this;
    }

    /**
     * Returns the title of the pop up window.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title of the pop up window.
     *
     * @param   string  $title
     * @return  $this
     */
    public function setTitle(string $title) : Confirmation
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Returns the description and context of the action about to be performed or cancelled.
     *
     * @return  string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Sets the description and context of the action about to be performed or cancelled.
     *
     * @param   string  $text
     * @return  $this
     */
    public function setText(string $text) : Confirmation
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Returns the text label for the button to continue with an action.
     *
     * @return  string
     */
    public function getOkText()
    {
        return $this->okText;
    }

    /**
     * Sets the text label for the button to continue with an action.
     *
     * @param   string  $okText
     * @return  $this
     */
    public function setOkText(string $okText)
    {
        $this->okText = $okText;

        return $this;
    }

    /**
     * Returns the text label for the button to cancel the action.
     *
     * @return  string
     */
    public function getDismissText()
    {
        return $this->dismissText;
    }

    /**
     * Sets the text label for the button to cancel the action.
     *
     * @param   string  $dismissText
     * @return  $this
     */
    public function setDismissText(string $dismissText)
    {
        $this->dismissText = $dismissText;

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
            'title'         => $this->getTitle(),
            'text'          => $this->getText(),
            'ok_text'       => $this->getOkText(),
            'dismiss_text'  => $this->getDismissText(),
        ];
    }
}
