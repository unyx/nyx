<?php namespace nyx\console\input;

// External dependencies
use nyx\core;

// Internal dependencies
use nyx\console;

/**
 * Input Parameter Definition
 *
 * Base class for concrete Argument and Option Definitions. Each input parameter has a name which is used to
 * access the parameter's value at runtime. Names are unique across parameter collections, eg. are unique at
 * runtime for a given invoked Command, but not necessarily across the whole Application.
 *
 * @package     Nyx\Console
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
abstract class Parameter implements core\interfaces\Named
{
    /**
     * The traits of a Input Parameter Definition.
     */
    use console\traits\Named;

    /**
     * @var string  The description of this Parameter.
     */
    private $description;

    /**
     * @var Value   The definition of this Parameter's Value.
     */
    private $value;

    /**
     * Creates a new Input Parameter Definition instance.
     *
     * @param   string  $name           The name of this Parameter.
     * @param   string  $description    A description of this Parameter.
     * @param   Value   $value          A definition of this Parameter's Value. If none is given, the Parameter
     *                                  will not accept any values.
     */
    public function __construct(string $name, string $description = null, Value $value = null)
    {
        $this->description = $description;

        // Make the name conform to our generic naming rules.
        $this->setName($name);

        if (isset($value)) {
            $this->setValue($value);
        }
    }

    /**
     * Returns the description of this Parameter.
     *
     * @return  string
     */
    public function getDescription() : ?string
    {
        return $this->description;
    }

    /**
     * Sets the description of this Parameter.
     *
     * @param   string  $description
     * @return  $this
     */
    public function setDescription(string $description) : Parameter
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Returns the definition of this Parameter's Value.
     *
     * @return  Value
     */
    public function getValue() : ?Value
    {
        return $this->value;
    }

    /**
     * Checks whether the Parameter's Value is defined, eg. whether the Parameter accepts any values at all.
     *
     * @return  bool
     */
    public function hasValue() : bool
    {
        return isset($this->value);
    }

    /**
     * Sets the definition of this Parameter's Value.
     *
     * Note the access scope, as the value definition should not be modified directly after getting assigned
     * to a Parameter, without the Parameter enforcing its own rules upon the Value.
     *
     * @param   Value   $value
     * @return  $this
     */
    protected function setValue(Value $value) : Parameter
    {
        $this->value = $value;

        return $this;
    }
}
