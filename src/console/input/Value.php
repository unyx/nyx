<?php namespace nyx\console\input;

// External dependencies
use nyx\core;

/**
 * Input Parameter Value Definition
 *
 * Defines what kind of values a parameter accepts: whether a parameter's value is optional or required to be set
 * and if it is optional, what the default value for the parameter is, if it was not given along with the input.
 *
 * Should a parameter not accept a value at all, a Value Definition must *not* be set for it. This is the case
 * for Options which act as behavioural flags for the application.
 *
 * @package     Nyx\Console
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 * @todo        The "valid" type which will pass through a validator callable, including support for
 *              custom error messages.
 */
class Value
{
    /**
     * The modes a Value can be of.
     */
    const REQUIRED  = 1;
    const OPTIONAL  = 2;

    /**
     * @var core\Mask   The mode mask of the Value.
     */
    private $mode;

    /**
     * @var mixed       The default value of the underlying Parameter. Only applies when the Value is optional.
     */
    private $default;

    /**
     * Constructs a new Input Parameter Value Definition instance.
     *
     * @param   int                         $mode       The mode of the Value (one of the class constants).
     * @param   mixed                       $default    The default value.
     * @throws  \InvalidArgumentException               When the given type of the Value is invalid (unrecognized).
     */
    public function __construct(int $mode, $default = null)
    {
        if ($mode > 2 || $mode < 1) {
            throw new \InvalidArgumentException("The given type of the value [$mode] is invalid.");
        }

        $this->mode = new core\Mask($mode);

        if (isset($default)) {
            $this->setDefault($default);
        }
    }

    /**
     * Compares this Value's mode to the given mode and returns true if the Value is in the given mode.
     *
     * @param   int $mode   The mode to check against.
     * @return  bool
     */
    public function is(int $mode) : bool
    {
        return $this->mode->is($mode);
    }

    /**
     * Returns the default value of the underlying Parameter.
     *
     * @return  mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Sets the default value for the underlying Parameter.
     *
     * @param   mixed               $default    The default value.
     * @return  $this
     * @throws  \InvalidArgumentException       When an invalid default value is given.
     */
    public function setDefault($default) : Value
    {
        if (isset($default) && !$this->mode->is(Value::OPTIONAL)) {
            throw new \InvalidArgumentException('Cannot set a default value for non-optional values.');
        }

        $this->default = $default;

        return $this;
    }
}
