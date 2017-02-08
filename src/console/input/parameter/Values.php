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
     * {@inheritdoc}
     *
     * @param   Definitions $definition     The Definitions of the Parameters that can be present in this collection.
     */
    public function __construct(Definitions $definition, $items = null)
    {
        $this->definitions = $definition;

        parent::__construct($items);
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
     */
    public function set($name, $value) : core\collections\interfaces\Map
    {
        if (!isset($name, $value)) {
            throw new \InvalidArgumentException('Input Values must be named and have a value that is not null.');
        }

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
     * Finalizes the Collection, populating it with Parameters that have not been explicitly set, but
     * have defined default values.
     *
     * @return  $this
     */
    public function finalize() : Values
    {
        // Not doing a simple array merge to preserve the key mapping of the parameters.
        foreach ($this->definitions->getDefaultValues() as $name => $value) {
            if (!isset($this->items[$name])) {
                $this->items[$name] = $value;
            }
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

    /**
     * {@inheritdoc}
     */
    protected function derive($items) : core\collections\interfaces\Collection
    {
        return new static($this->definitions, $items);
    }
}
