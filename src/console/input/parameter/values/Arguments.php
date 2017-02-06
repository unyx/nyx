<?php namespace nyx\console\input\parameter\values;

// External dependencies
use nyx\core;

// Internal dependencies
use nyx\console\input\parameter;
use nyx\console\input\exceptions;

/**
 * Input Arguments
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Arguments extends parameter\Values
{
    /**
     * @var parameter\definitions\Arguments   The Definitions of the Arguments that can be present in this collection.
     */
    protected $definitions;

    /**
     * {@inheritdoc}
     *
     * Overridden to enforce a stricter Definitions collection type.
     */
    public function __construct(parameter\definitions\Arguments $definition)
    {
        parent::__construct($definition);
    }

    /**
     * Adds an argument's value to the collection.
     *
     * Automatically decides on the name of the argument based on the present definition.
     *
     * @param   string  $value                  The argument's value to set.
     * @param   $this
     * @throws  exceptions\ArgumentsTooMany     When the definition does not permit any further arguments.
     */
    public function push(string $value) : core\collections\interfaces\Map
    {
        // Grab an Argument for the next index. If we get null here, it means there are no further Arguments
        // that accept values present.
        if (!$argument = $this->definitions->getNextDefinition($this)) {
            throw new exceptions\ArgumentsTooMany($this);
        }

        // Now that we know how to map the Argument via its name, we can safely set its value. Set() will also
        // resolve the case of parameters accepting multiple values.
        return $this->set($argument->getName(), $value);
    }

    /**
     * {@inheritdoc}
     *
     * Overridden to include validation in the finalize step, while ensuring the Collection is valid
     * already before being populated with default values for not explicitly set Arguments.
     */
    public function finalize() : parameter\Values
    {
        $this->validate();

        return parent::finalize();
    }

    /**
     * Validates this collection.
     *
     * Checks if the Collection contains all necessary arguments. We are not validating whether there are
     * too many arguments as this can only happen when push()'ing values directly, which is handled inside
     * that method as well.
     *
     * @throws  exceptions\ArgumentsNotEnough   When not enough arguments are present in this Collection.
     */
    protected function validate()
    {
        if ($this->count() < $this->definitions->required()) {
            throw new exceptions\ArgumentsNotEnough($this);
        }
    }
}
