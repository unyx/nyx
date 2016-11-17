<?php namespace nyx\notify\transports\slack\message\attachment;

// External dependencies
use nyx\core;
use nyx\diagnostics;

/**
 * Slack Action
 *
 * See {@see https://api.slack.com/docs/message-buttons} for Slack's documentation on using Messages with Actions.
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 * @todo        PHP7.1 pass for nullable return types (missing ?string on most methods).
 */
class Action implements core\interfaces\Serializable
{
    /**
     * The traits of an Action.
     */
    use core\traits\Serializable;

    /**
     * The types an Action can be of. Note: Currently Slack only provides the 'button' action.
     */
    const TYPE_BUTTON = 'button';

    /**
     * The styles a button can be of.
     */
    const STYLE_DEFAULT = 'default';
    const STYLE_PRIMARY = 'primary';
    const STYLE_DANGER  = 'danger';

    /**
     * @var string  Required. The name of the Action. This name will be returned to your Action URL along with the message's
     *              callback_id when this action is invoked.
     */
    protected $name;

    /**
     * @var string  Required. The user-facing label for the message button representing this action. Cannot contain
     *              any markup.
     */
    protected $text;

    /**
     * @var string  Required. The type of the Action. One of the TYPE_* class constants.
     */
    protected $type = self::TYPE_BUTTON;

    /**
     * @var string  Optional. The style of the Action. One of the STYLE_* class constants.
     */
    protected $style;

    /**
     * @var string  Optional. The value of the Action - a string identifying this specific action. It will be sent
     *              to your Action URL along with the name and attachment's callback_id. If providing multiple
     *              actions with the same name, the $value can be strategically used to differentiate intent.
     */
    protected $value;

    /**
     * @var actions\Confirmation    Optional. The Confirmation field for this Action.
     */
    protected $confirm;

    /**
     * Creates a new Action instance.
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
     * Sets the attributes of this Action.
     *
     * @param   array   $attributes
     * @return  $this
     */
    public function setAttributes(array $attributes) : Action
    {
        if (isset($attributes['name'])) {
            $this->setName($attributes['name']);
        }

        if (isset($attributes['text'])) {
            $this->setText($attributes['text']);
        }

        if (isset($attributes['type'])) {
            $this->setType($attributes['type']);
        }

        if (isset($attributes['style'])) {
            $this->setStyle($attributes['style']);
        }

        if (isset($attributes['value'])) {
            $this->setValue($attributes['value']);
        }

        if (isset($attributes['confirm'])) {
            $this->setConfirm($attributes['confirm']);
        }

        return $this;
    }

    /**
     * Returns the name of the Action.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name of the Action.
     *
     * @param string $name
     * @return  $this
     */
    public function setName(string $name) : Action
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns the user-facing label for the message button representing this action.
     *
     * @return  string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Sets the user-facing label for the message button representing this action.
     *
     * @param   string  $text
     * @return  $this
     */
    public function setText(string $text) : Action
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Returns the style of the Action. One of the STYLE_* class constants.
     *
     * @return  string
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * Sets the style of the Action. One of the STYLE_* class constants.
     *
     * @param   string  $style
     * @return  $this
     */
    public function setStyle(string $style) : Action
    {
        $this->style = $style;

        return $this;
    }

    /**
     * Returns the type of the Action. One of the TYPE_* class constants.
     *
     * @return  string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the type of the Action. One of the TYPE_* class constants.
     *
     * @param   string  $type
     * @return  $this
     */
    public function setType(string $type) : Action
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Returns the value of the Action.
     *
     * @return  string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the value of the Action.
     *
     * @param   string  $value
     * @return  $this
     */
    public function setValue(string $value) : Action
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Returns the Confirmation field for this Action.
     *
     * @return  actions\Confirmation
     */
    public function getConfirm()
    {
        return $this->confirm;
    }

    /**
     * Sets the Confirmation field for this Action.
     *
     * @param   array|actions\Confirmation  $confirm
     * @return  $this
     * @throws  \InvalidArgumentException
     */
    public function setConfirm($confirm) : Action
    {
        if (is_array($confirm)) {
            $this->confirm = new actions\Confirmation($confirm);

            return $this;
        }

        if ($confirm instanceof actions\Confirmation) {
            $this->confirm = $confirm;

            return $this;
        }

        throw new \InvalidArgumentException("Expected an array or an instance of ".actions\Confirmation::class.", got [".diagnostics\Debug::getTypeName($confirm)."] instead.");
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
            'name'    => $this->getName(),
            'text'    => $this->getText(),
            'style'   => $this->getStyle(),
            'type'    => $this->getType(),
            'value'   => $this->getValue()
        ];

        if (null !== $confirm = $this->getConfirm()) {
            $data['confirm'] = $confirm->toArray();
        }

        return $data;
    }
}
