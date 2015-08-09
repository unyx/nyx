<?php namespace nyx\core\collections\traits;

// Internal dependencies
use nyx\core\collections\interfaces;

/**
 * Map
 *
 * Allows for the implementation of the collections\interfaces\Map interface.
 *
 * @package     Nyx\Core\Collections
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/core/collections.html
 */
trait Map
{
    /**
     * The traits of a Map trait.
     */
    use Collection, ArrayAccess;

    /**
     * @see interfaces\Map::set()
     */
    public function set($key, $item) : self
    {
        $this->items[$key] = $item;

        return $this;
    }

    /**
     * @see interfaces\Collection::replace()
     */
    public function replace($items) : self
    {
        $this->items = [];

        foreach ($this->extractItems($items) as $key => $item) {
            $this->set($key, $item);
        }

        return $this;
    }

    /**
     * @see self::set()
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }
}
