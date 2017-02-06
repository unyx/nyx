<?php namespace nyx\console\input\parameter;

// External dependencies
use nyx\core;

// Internal dependencies
use nyx\console\input;

/**
 * Input Values
 *
 * Base class for Input Argument and Option value collections.
 *
 * Those collections are bound by their definitions and as such parameters that are not defined cannot be set.
 * They can also not escape their definition, eg. a definition may only be set during construction to avoid
 * mismatches between the master and the bag definitions referenced in Values collections.
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
abstract class Values extends core\collections\Map
{
    /**
     * @var Definitions  The Definitions of the Parameters that can be present in this collection.
     */
    protected $definitions;

    /**
     * Constructs an Input Values instance.
     *
     * @param   Definitions $definition     The Definitions of the Parameters that can be present in this collection.
     */
    public function __construct(Definitions $definition)
    {
        $this->definitions = $definition;
    }

    /**
     * Returns the Definitions of the Parameters that can be present in this collection.
     *
     * @return  Definitions
     */
    public function definitions() : Definitions
    {
        return $this->definitions;
    }

    /**
     * {@inheritdoc}
     *
     * Overridden to populate the resulting array with default values for Parameters that are
     * not actually set.
     */
    public function all() : array
    {
        $return = $this->items;

        // Not doing a simple array merge to preserve the key mapping of the parameters.
        foreach ($this->definitions->getDefaultValues() as $name => $value) {
            if (!isset($return[$name])) {
                $return[$name] = $value;
            }
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     *
     * Overridden to fall back to the default value defined for the given parameter if no value has been
     * set for it and no $default has been given either.
     */
    public function get($name, $default = null)
    {
        $parameter = $this->assertIsDefined($name);

        // If a value for the given Parameter has been set, return it. Otherwise the default value
        // passed to this method takes precedence over default values set in the Value's Definition, if any.
        return $this->items[$name] ?? ($default ?? (($value = $parameter->getValue()) ? $value->getDefault() : $default));
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value) : core\collections\interfaces\Map
    {
        $parameter = $this->assertIsDefined($name);

        // When dealing with a multi-value parameter, push the value into an array instead of just setting it,
        // unless it's an array itself (in which case it will override the currently set array).
        if ($parameter->getValue() instanceof input\values\Multiple && !is_array($value)) {
            $this->items[$name][] = $value;
        } else {
            $this->items[$name] = $value;
        }

        return $this;
    }

    /**
     * Asserts a Parameter with the given name is defined for this collection and returns it.
     *
     * @param   string  $name           The name of the Parameter.
     * @throws  \OutOfBoundsException   When no Parameter with the given name has been defined.
     * @return  input\Parameter
     */
    protected function assertIsDefined(string $name) : input\Parameter
    {
        /* @var input\Parameter $parameter */
        if (!$parameter = $this->definitions->get($name)) {
            throw new \OutOfBoundsException("The parameter [$name] is not defined.");
        }

        return $parameter;
    }
}
