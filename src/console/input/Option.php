<?php namespace nyx\console\input;

// External dependencies
use nyx\core;

/**
 * Input Option Definition
 *
 * @package     Nyx\Console
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Option extends Parameter
{
    /**
     * @var string      The shortcut name of this option.
     */
    private $shortcut;

    /**
     * @var callable    A callback to execute when the Option gets set with a non-null value.
     */
    private $callback;

    /**
     * {@inheritDoc}
     *
     * Overridden to allow shortcuts and callbacks.
     *
     * @param   string      $shortcut           The shortcut. A hyphen at the beginning will be removed and only the
     *                                          first character will be used, ie. a string of "-ve" will result in the
     *                                          shortcut "v".
     * @param   callable    $callback           A callback to execute when the Option gets set with a non-null value.
     * @throws  \InvalidArgumentException       If the shortcut is set but empty.
     */
    public function __construct(string $name, string $shortcut = null, string $description = null, Value $value = null, callable $callback = null)
    {
        // If a shortcut is given, standardize its name - remove any dashes at the beginning.
        // We might have ended with an empty string if it only contained dashes.
        if (!empty($shortcut) && empty($shortcut = ltrim($shortcut, '-')[0])) {
            throw new \InvalidArgumentException("An option's shortcut cannot be empty when set.");
        }

        $this->shortcut = $shortcut;
        $this->callback = $callback;

        parent::__construct($name, $description, $value);
    }

    /**
     * {@inheritDoc}
     *
     * Overriding the Named trait to remove unnecessary dashes from the beginning of the string. Gets called
     * automatically in the parent's constructor.
     */
    public function setName(string $name) : core\interfaces\Named
    {
        return parent::setName(ltrim($name, '-'));
    }

    /**
     * Returns the shortcut name of this Option.
     *
     * @return  string
     */
    public function getShortcut() : ?string
    {
        return $this->shortcut;
    }

    /**
     * Returns the callback to execute when the Option gets set with a non-null value.
     *
     * @return  callable
     */
    public function getCallback() : ?callable
    {
        return $this->callback;
    }

    /**
     * Sets a callback to execute when the Option gets set with a non-null value.
     *
     * @param   callable    $callback
     */
    public function setCallback(callable $callback) : Option
    {
        $this->callback = $callback;

        return $this;
    }
}
