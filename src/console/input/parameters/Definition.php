<?php namespace nyx\console\input\parameters;

// External dependencies
use nyx\core\collections;

/**
 * Input Parameters Definition
 *
 * Represents a Definition for a set of Input Parameters, either Arguments or Options.
 *
 * @package     Nyx\Console
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
abstract class Definition implements \IteratorAggregate, collections\interfaces\NamedObjectSet
{
    /**
     * The traits of a Definition Bag instance.
     */
    use collections\traits\NamedObjectSet;

    /**
     * Returns the default values of all items in the Bag.
     *
     * @return  array
     */
    public function getDefaults() : array
    {
        $values = [];

        /* @var \nyx\console\input\Parameter $item */
        foreach ($this->items as $item) {
            $values[$item->getName()] = $item->getValue()->getDefault();
        }

        return $values;
    }
}
