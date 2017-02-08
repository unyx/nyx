<?php namespace nyx\console\input\parameter\values;

// External dependencies
use nyx\core;

// Internal dependencies
use nyx\console\input\parameter;
use nyx\console\input;

/**
 * Input Options
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Options extends parameter\Values
{
    /**
     * {@inheritdoc}
     *
     * Overridden to enforce a stricter Definitions collection type.
     */
    public function __construct(parameter\definitions\Options $definition)
    {
        parent::__construct($definition);
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value) : core\collections\interfaces\Map
    {
        $option = $this->assertIsDefined($name);

        // Handle value expectations appropriately.
        if (!$expected = $option->getValue()) {
            if (isset($value)) {
                throw new \RuntimeException("The option [--$name] does not take any values.");
            }
        } elseif (isset($value)) {
            if ($expected->is(input\Value::REQUIRED)) {
                throw new \RuntimeException("The option [--$name] requires a value.");
            }

            // Grab the default value in this case, since we're dealing with an optional value that was
            // not explicitly set.
            $value = $expected->getDefault();
        }

        // Slight overhead, because the parent will grab the definition again, but at the same time
        // it handles multiple value parameters for us. Setting boolean true here if $value is still
        // not set, which covers Options which do not accept values but still get set (ie. flags).
        return parent::set($name, $value ?? true);
    }
}
