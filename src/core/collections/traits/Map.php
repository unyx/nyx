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
    use Collection;

    /**
     * @see interfaces\Map::get()
     */
    public function get($key, $default = null)
    {
        return $this->items[$key] ?? $default;
    }

    /**
     * @see interfaces\Map::set()
     */
    public function set($key, $item) : self
    {
        if (null === $item) {
            throw new \InvalidArgumentException("Items in a Sequence cannot have a value of null.");
        }

        $this->items[$key] = $item;

        return $this;
    }

    /**
     * @see interfaces\Map::has()
     */
    public function has($key) : bool
    {
        return isset($this->items[$key]);
    }

    /**
     * @see interfaces\Collection::contains()
     */
    public function contains($item) : bool
    {
        return empty($this->items) ? false : (null !== $this->key($item));
    }

    /**
     * @see interfaces\Map::remove()
     */
    public function remove($key) : self
    {
        unset($this->items[$key]);

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
     * @see interfaces\Map::key()
     */
    public function key($item)
    {
        foreach ($this->items as $key => $value) {
            if ($value === $item) {
                return $key;
            }
        }

        return null;
    }

    /**
     * @see interfaces\Map::keys()
     */
    public function keys($of = null) : array
    {
        return array_keys($this->items, $of, true);
    }

    /**
     * @see interfaces\Map::values()
     */
    public function values() : array
    {
        return array_values($this->items);
    }

    /**
     * Magic alias for {@see self::get()}.
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Magic alias for {@see self::set()}.
     */
    public function __set($key, $item)
    {
        $this->set($key, $item);
    }

    /**
     * Magic alias for {@see self::has()}.
     */
    public function __isset($key) : bool
    {
        return $this->has($key);
    }

    /**
     * Magic alias for {@see self::has()}.
     */
    public function __unset($key)
    {
        return $this->remove($key);
    }

    /**
     * @see self::get()
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * @see self::set()
     */
    public function offsetSet($key, $item)
    {
        return $this->set($key, $item);
    }

    /**
     * @see self::has()
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * @see self::remove()
     */
    public function offsetUnset($key)
    {
        return $this->remove($key);
    }
}
