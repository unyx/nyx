<?php namespace nyx\console\input\parameter;

// External dependencies
use nyx\core;

/**
 * Input Parameter Definitions
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
abstract class Definitions extends core\collections\NamedObjectSet
{
    /**
     * Returns the default values of all Parameters defined in this collection.
     *
     * @return  array
     */
    public function getDefaultValues() : array
    {
        $values = [];

        /* @var \nyx\console\input\Parameter $parameter */
        foreach ($this->items as $name => $parameter) {
            // In the case of Parameters which do not accept values (ie. optional flags), we explicitly default them
            // to a boolean false, opposed to a boolean true when they are actually set in input.
            $values[$name] = ($value = $parameter->getValue()) ? $value->getDefault() : false;
        }

        return $values;
    }
}
