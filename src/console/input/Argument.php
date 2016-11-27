<?php namespace nyx\console\input;

/**
 * Input Argument Definition
 *
 * @package     Nyx\Console
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Argument extends Parameter
{
    /**
     * {@inheritDoc}
     *
     * Overridden to ensure Arguments require a value by default.
     */
    public function __construct(string $name, string $description = null, Value $value = null)
    {
        parent::__construct($name, $description, $value ?? new Value(Value::REQUIRED));
    }

    /**
     * {@inheritDoc}
     */
    protected function setValue(Value $value)
    {
        // An argument must accept a value. If you don't want to accept a value, don't define it.
        // Use an Option instead. Simple as that.
        if ($value->is(Value::NONE)) {
            throw new \InvalidArgumentException("A defined argument [{$this->getName()}] must accept a value.");
        }

        parent::setValue($value);
    }
}
