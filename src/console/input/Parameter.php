<?php namespace nyx\console\input;

// Internal dependencies
use nyx\console\traits;

/**
 * Input Parameter Definition
 *
 * Base building block for concrete Argument and Option Definitions. Each input parameter has a name
 * which is used to access the parameter's value at runtime. Names are unique across parameter collections,
 * ie. are unique at runtime for a given invoked Command, but not necessarily across the whole Application.
 *
 * @package     Nyx\Console\Input
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/console/index.html
 */
abstract class Parameter
{
    /**
     * The traits of an Input Parameter Definition.
     */
    use traits\Named;

    /**
     * @var string  The description of this Parameter.
     */
    private $description;

    /**
     * @var Value   The Value definition for this Parameter.
     */
    private $value;

    /**
     * Constructs a new Input Parameter Definition.
     *
     * @param   string  $name           The name of this Parameter.
     * @param   string  $description    A description of this Parameter.
     * @param   Value   $value          A Value Definition for this Parameter.
     */
    public function __construct(string $name, string $description = null, Value $value = null)
    {
        $this->description = $description;

        // Make the name conform to our generic naming rules.
        $this->setName($name);

        // Use the given Value or create a new definition with sane defaults.
        $this->setValue($value ?: new Value());
    }

    /**
     * Returns the description of this Parameter.
     *
     * @return  string
     */
    public function getDescription() : string
    {
        return $this->description;
    }

    /**
     * Sets the description of this Parameter.
     *
     * @param   string  $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * Returns the Value definition assigned to this Parameter.
     *
     * @return  Value
     */
    public function getValue() : Value
    {
        return $this->value;
    }

    /**
     * Sets the Value definition assigned to this Parameter.
     *
     * Note the access scope, as the value definition should not be modified directly after getting assigned
     * to a Parameter, without the Parameter enforcing its own rules upon the Value.
     *
     * @param   Value   $value
     */
    protected function setValue(Value $value)
    {
        $this->value = $value;
    }
}
