<?php namespace nyx\core\collections\traits;

// Internal dependencies
use nyx\core\collections\interfaces;

/**
 * Set
 *
 * Allows for the implementation of the collections\interfaces\Set interface.
 *
 * @package     Nyx\Console\Application
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/core/collections.html
 */
trait Set
{
    /**
     * The traits of a Set trait.
     */
    use Collection;

    /**
     * @see interfaces\Set::set()
     */
    public function set($item) : self
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @see interfaces\Collection::replace()
     */
    public function replace($items) : self
    {
        $this->items = [];

        foreach ($this->extractItems($items) as $item) {
            $this->set($item);
        }

        return $this;
    }
}
