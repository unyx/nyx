<?php namespace nyx\console\input\parameter\definitions;

// External dependencies
use nyx\core;
use nyx\diagnostics;

// Internal dependencies
use nyx\console\input;

/**
 * Input Argument Definitions
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Arguments extends input\parameter\Definitions
{
    /**
     * @var int     The number of arguments required to be present in an Input Arguments collection for it to
     *              to conform to the Definitions in this collection.
     */
    private $required = 0;

    /**
     * @var bool    Whether one of the arguments accepts multiple values and therefore must be the last argument
     *              present in the definition.
     */
    private $hasMultiparam = false;

    /**
     * @var bool    Whether one of the arguments is optional, meaning no more required arguments can be defined.
     */
    private $hasOptional = false;

    /**
     * {@inheritdoc}
     */
    public function __construct($arguments = null)
    {
        $this->setCollectedType(input\Argument::class);

        parent::__construct($arguments);
    }

    /**
     * Returns the next Input Argument Definition for the given Input Argument values collection,
     * unless the collection already exceeds the number of defined Arguments.
     *
     * @param   input\parameter\values\Arguments    $values
     * @return  input\Argument
     */
    public function getNextDefinition(input\parameter\values\Arguments $values) : ?input\Argument
    {
        $nextIndex = $values->count();

        // We are using an associative array internally but we need to match by a numeric index
        // in this case, maybe even twice, so let's only grab the values once for that.
        /* @var input\Argument[] $items */
        $items = array_values($this->items);

        // $items is now 0-indexed, while $nextIndex is a count of all values already set,
        // meaning it's 1-indexed.
        if (isset($items[$nextIndex])) {
            return $items[$nextIndex];
        }

        // At this point, there was no further Argument definition present. However, maybe the previous
        // Argument accepts multiple values and we are actually supposed to add a value to it?
        if (isset($items[$nextIndex - 1]) && $items[$nextIndex - 1]->getValue() instanceof input\values\Multiple) {
            return $items[$nextIndex - 1];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function replace($items) : core\collections\interfaces\Collection
    {
        $this->hasMultiparam = false;
        $this->hasOptional   = false;
        $this->required      = 0;

        foreach ($this->extractItems($items) as $item) {

            // Allow for defining Argument instances without explicitly instantiating them
            // when creating Input Definitions.
            if (is_array($item)) {
                $item = new input\Argument(...$item);
            }

            $this->add($item);
        }

        return $this;
    }

    /**
     * Adds a Input Argument Definition to this collection.
     *
     * @param   input\Argument              $argument   The argument definition.
     * @throws  \InvalidArgumentException               When the argument's type is invalid.
     * @throws  \LogicException                         When an incorrect argument was given.
     * @return  $this
     */
    public function add(core\interfaces\Named $argument) : core\collections\interfaces\NamedObjectSet
    {
        // The Collection is locked once populated with an Argument accepting multiple values and since we don't allow
        // overwriting by setting, might as well check for this here already.
        if ($this->hasMultiparam) {
            throw new \LogicException("Cannot define additional arguments after an Argument [name: ".end($this->items)->getName()."] which accepts multiple values.");
        }

        // Make sure we got the proper type.
        if (!$argument instanceof input\Argument) {
            throw new \InvalidArgumentException('Expected an instance of ['.input\Argument::class.'], got ['.diagnostics\Debug::getTypeName($argument).'] instead.');
        }

        // We'll need those a few times.
        $name  = $argument->getName();
        $value = $argument->getValue();

        // Arguments are stored by name so no duplicates allowed here.
        if (isset($this->items[$name])) {
            throw new \LogicException("An Argument with this name [$name] has already been defined.");
        }

        // Keep track of how many Arguments we will require to be present in the Input later on.
        if ($value->is(input\Value::REQUIRED)) {
            if ($this->hasOptional) {
                throw new \LogicException("Cannot add a required Argument after an optional one.");
            }

            ++$this->required;
        } else {
            $this->hasOptional = true;
        }

        // If the Argument accepts multiple values, this effectively requires it to be the last Argument in
        // the definition, which locks this Bag.
        if ($value instanceof input\values\Multiple) {
            $this->hasMultiparam = true;
        }

        // Finally, store the Argument.
        $this->items[$name] = $argument;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * Overridden to update the counters and flags we're keeping track of internally.
     */
    public function remove(string $name) : core\collections\interfaces\NamedObjectSet
    {
        // If no Argument is defined for the given name, there's nothing to remove.
        if (!isset($this->items[$name])) {
            return $this;
        }

        /* @var input\Argument $argument */
        $value = $argument->getValue();

        // There's at most one Multiple Value accepting Argument so we can remove that flag safely.
        if ($value instanceof input\values\Multiple) {
            $this->hasMultiparam = false;
        }

        // Reduce our counter of required arguments.
        if ($value->is(input\Value::REQUIRED)) {
            $this->required--;
        }
        // @fixme - We're not checking whether other Arguments are optional.
        else {
            $this->hasOptional = true;
        }

        return parent::remove($name);
    }

    /**
     * Returns the number of Arguments defined in this collection.
     *
     * @return  int
     */
    public function count() : int
    {
        return $this->hasMultiparam ? PHP_INT_MAX : count($this->items);
    }

    /**
     * Returns the number of arguments required to be present in an Input Arguments collection for it to
     * to conform to the Definitions in this collection.
     *
     * @return  int
     */
    public function required() : int
    {
        return $this->required;
    }
}
