<?php namespace nyx\core\collections\traits;

// External dependencies
use nyx\utils;

// Internal dependencies
use nyx\core\collections\interfaces;
use nyx\core;

/**
 * Collection
 *
 * A Collection is an object that contains other items which can be set, get and removed from the Collection.
 *
 * Usage of this trait allows you to implement \IteratorAggregate and the interfaces\Collection interface, including
 * all of its inherited interfaces.
 *
 * Important notes:
 * 1) null is *not* an acceptable value for an item within a Collection. Null is used internally by many methods
 *    to denote an item that is *not set*. Likewise the methods will bombard you with exceptions if you attempt
 *    to set an item with null as its value. This is done to ensure the return values of the API are consistent
 *    and also provides a slight performance gain for some methods;
 * 2) Some of the methods, like self::map() or self::filter() for instance, make assumptions as to the constructor
 *    of the exhibitor of this trait, assuming that it accepts a Collection, Arrayable object or array as
 *    its first argument;
 *
 * @package     Nyx\Core\Collections
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/core/collections.html
 * @todo        Lo-dash-style find() / findWhere() (pluck and where).
 */
trait Collection
{
    /**
     * The traits of a Collection trait.
     */
    use core\traits\Serializable;

    /**
     * @var array   An array of the items contained within the object exhibiting this trait, ie. the concrete
     *              Collection.
     */
    protected $items = [];

    /**
     * @see \nyx\core\collections\interfaces\Collection::all()
     */
    public function all() : array
    {
        return $this->items;
    }

    /**
     * @see \nyx\core\collections\interfaces\Collection::find()
     */
    public function find(callable $callback, $default = null)
    {
        return utils\Arr::find($this->items, $callback, $default);
    }

    /**
     * @see \nyx\core\collections\interfaces\Collection::first()
     */
    public function first($callback = false, $default = null)
    {
        return utils\Arr::first($this->items, $callback, $default);
    }

    /**
     * @see \nyx\core\collections\interfaces\Collection::last()
     */
    public function last($callback = false, $default = null)
    {
        return utils\Arr::last($this->items, $callback, $default);
    }

    /**
     * @see \nyx\core\collections\interfaces\Collection::initial()
     */
    public function initial($callback = false, $default = null)
    {
        return utils\Arr::initial($this->items, $callback, $default);
    }

    /**
     * @see \nyx\core\collections\interfaces\Collection::rest()
     */
    public function rest($callback = false, $default = null)
    {
        return utils\Arr::rest($this->items, $callback, $default);
    }

    /**
     * @see \nyx\core\collections\interfaces\Collection::slice()
     */
    public function slice(int $offset, int $length = null, bool $preserveKeys = false) : interfaces\Collection
    {
        return new static(array_slice($this->items, $offset, $length, $preserveKeys));
    }

    /**
     * @see \nyx\core\collections\interfaces\Collection::pluck()
     */
    public function pluck($key, $index = null) : array
    {
        return utils\Arr::pluck($this->items, $key, $index);
    }

    /**
     * @see \nyx\core\collections\interfaces\Collection::select()
     */
    public function select(callable $callback) : interfaces\Collection
    {
        return new static(array_filter($this->items, $callback));
    }

    /**
     * @see \nyx\core\collections\interfaces\Collection::reject()
     *
     * Note: Usage of self::select() with the comparison in your callback inverted is preferred as this method is
     * much slower than simply running array_filter.
     */
    public function reject(callable $callback) : interfaces\Collection
    {
        $result = [];

        foreach ($this->items as $key => $item) {
            if (!call_user_func($callback, $item)) {
                $result[$key] = $item;
            }
        }

        return new static($result);
    }

    /**
     * @see \nyx\core\collections\interfaces\Collection::map()
     */
    public function map(callable $callback) : interfaces\Collection
    {
        return new static(array_map($callback, $this->items, array_keys($this->items)));
    }

    /**
     * @see \nyx\core\collections\interfaces\Collection::each()
     */
    public function each(callable $callback) : self
    {
        array_walk($this->items, $callback);

        return $this;
    }

    /**
     * @see \nyx\core\collections\interfaces\Collection::reduce()
     */
    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->items, $callback, $initial);
    }

    /**
     * @see \nyx\core\collections\interfaces\Collection::implode()
     */
    public function implode($value, string $glue = '') : string
    {
        return implode($glue, $this->pluck($value));
    }

    /**
     * @see \nyx\core\collections\interfaces\Collection::reverse()
     */
    public function reverse() : interfaces\Collection
    {
        return new static(array_reverse($this->items));
    }

    /**
     * @see \nyx\core\collections\interfaces\Collection::collapse()
     */
    public function collapse() : interfaces\Collection
    {
        return new static(utils\Arr::collapse($this->items));
    }

    /**
     * @see \nyx\core\collections\interfaces\Collection::flatten()
     */
    public function flatten() : interfaces\Collection
    {
        return new static(utils\Arr::flatten($this->items));
    }

    /**
     * @see \nyx\core\collections\interfaces\Collection::fetch()
     */
    public function fetch($key) : interfaces\Collection
    {
        return new static(utils\Arr::fetch($this->items, $key));
    }

    /**
     * @see \nyx\core\collections\interfaces\Collection::merge()
     */
    public function merge(...$with) : interfaces\Collection
    {
        $result = $this->items;

        foreach ($with as $items) {
            $result = array_merge($result, $this->extractItems($items));
        }

        return new static($result);
    }

    /**
     * @see \nyx\core\collections\interfaces\Collection::diff()
     */
    public function diff(...$against)
    {
        $result = $this->items;

        foreach ($against as $items) {
            $result = array_merge($result, $this->extractItems($items));
        }

        return new static($result);
    }

    /**
     * @see \nyx\core\collections\interfaces\Collection::isEmpty()
     */
    public function isEmpty() : bool
    {
        return empty($this->items);
    }

    /**
     * @see \Countable::count()
     */
    public function count() : int
    {
        return count($this->items);
    }

    /**
     * Returns an Iterator for the items in this Collection. Allows for the implementation of \IteratorAggregate.
     *
     * @return  \ArrayIterator
     */
    public function getIterator() : \ArrayIterator
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($data)
    {
        $this->items = unserialize($data);
    }

    /**
     * @see \nyx\core\interfaces\Arrayable::toArray()
     */
    public function toArray() : array
    {
        return array_map(function($value) {
            return $value instanceof core\interfaces\Arrayable ? $value->toArray() : $value;
        }, $this->items);
    }

    /**
     * Make sure we're able to handle deep copies properly. This will work for instances of the exhibitor of this
     * trait contained within the exhibitor's Collection itself, but may require overrides for customized
     * Collections.
     */
    public function __clone()
    {
        foreach ($this->items as $key => $value) {
            if ($value instanceof interfaces\Collection) {
                $this->items[$key] = clone $value;
            }
        }
    }

    /**
     * Inspects the given $items and attempts to figure out whether and how to extract its elements or whether
     * to simply cast the variable to an array to make use of it.
     *
     * @param   mixed   $items
     * @return  array
     */
    protected function extractItems($items) : array
    {
        // If we were given an object implementing the Collection interface, grab all its items, preserving
        // the keys.
        if ($items instanceof interfaces\Collection) {
            return $items->all();
        }

        // If we were given an object that is Arrayable, convert it to an array using the exposed method.
        if ($items instanceof core\interfaces\Arrayable) {
            return $items->toArray();
        }

        // Worst case scenario - use PHP's internals to cast it to an array.
        return (array) $items;
    }
}
