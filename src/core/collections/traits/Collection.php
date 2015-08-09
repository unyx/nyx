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
 * Usage of this trait allows you to implement the interfaces\Collection interface and \IteratorAggregate.
 *
 * Important notes:
 * 1) null is *not* an acceptable value for an item within a Collection. Null is used internally by many methods
 *    to denote an item that is *not set*. Likewise the methods will bombard you with exceptions if you attempt
 *    to set an item with null as its value. This is done to ensure the return values of the API are consistent
 *    and also provides a slight performance gain for some methods.
 * 2) Some of the methods, like self::map() or self::filter() for instance, make assumptions as to the constructor
 *    of the exhibitor of this trait, assuming that it accepts a Collection, Arrayable object or array as
 *    its first argument.
 * 3) For simplicity and performance reasons, some of the methods do not rely on each other to reduce some
 *    overhead of additional function calls. This is the case, for instance, for self::get(), which does not make
 *    use of self::has() to check for the existence of an item or self::replace() which will not call self::set()
 *    for each item passed to it. Keep this in mind when overriding them.
 *
 * @package     Nyx\Core\Collections
 * @version     0.0.8
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/core/collections.html
 * @todo        Lo-dash-style find() / findWhere() (pluck and where).
 * @todo        Decide: Regarding important note #2 - make use of the respective methods internally?
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
     * @see interfaces\Collection::get()
     */
    public function get($key, $default = null)
    {
        if (isset($key, $this->items)) {
            return $this->items[$key];
        }

        return $default;
    }

    /**
     * @see interfaces\Collection::has()
     */
    public function has($key) : bool
    {
        return isset($key, $this->items);
    }

    /**
     * @see interfaces\Collection::contains()
     */
    public function contains($item) : bool
    {
        return null !== $this->key($item);
    }

    /**
     * @see interfaces\Collection::key()
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
     * @see interfaces\Collection::remove()
     */
    public function remove($key) : self
    {
        unset($this->items[$key]);

        return $this;
    }

    /**
     * @see interfaces\Collection::all()
     */
    public function all() : array
    {
        return $this->items;
    }

    /**
     * @see interfaces\Collection::values()
     */
    public function values() : array
    {
        return array_values($this->items);
    }

    /**
     * @see interfaces\Collection::keys()
     */
    public function keys($of = null) : array
    {
        return array_keys($this->items, $of, true);
    }

    /**
     * Pushes an item onto the beginning of the Collection.
     *
     * @param   mixed  $value   The item to push into the Collection.
     */
    public function prepend($value)
    {
        array_unshift($this->items, $value);
    }

    /**
     * Returns and then removes the first item from the Collection.
     *
     * @return  mixed|null
     */
    public function shift()
    {
        return array_shift($this->items);
    }

    /**
     * Pushes an item onto the the end of the Collection.
     *
     * @param   mixed  $value   The item to push into the Collection.
     */
    public function push($value)
    {
        $this->items[] = $value;
    }

    /**
     * Returns and then removes the last item from the Collection.
     *
     * @return  mixed|null
     */
    public function pop()
    {
        return array_pop($this->items);
    }

    /**
     * @see interfaces\Collection::find()
     */
    public function find(callable $callback, $default = null)
    {
        return utils\Arr::find($this->items, $callback, $default);
    }

    /**
     * @see interfaces\Collection::first()
     */
    public function first($callback = false, $default = null)
    {
        return utils\Arr::first($this->items, $callback, $default);
    }

    /**
     * @see interfaces\Collection::last()
     */
    public function last($callback = false, $default = null)
    {
        return utils\Arr::last($this->items, $callback, $default);
    }

    /**
     * @see interfaces\Collection::initial()
     */
    public function initial($callback = false, $default = null)
    {
        return utils\Arr::initial($this->items, $callback, $default);
    }

    /**
     * @see interfaces\Collection::rest()
     */
    public function rest($callback = false, $default = null)
    {
        return utils\Arr::rest($this->items, $callback, $default);
    }

    /**
     * @see interfaces\Collection::slice()
     */
    public function slice(int $offset, int $length = null, bool $preserveKeys = false) : interfaces\Collection
    {
        return new static(array_slice($this->items, $offset, $length, $preserveKeys));
    }

    /**
     * @see interfaces\Collection::pluck()
     */
    public function pluck($key, $index = null) : array
    {
        return utils\Arr::pluck($this->items, $key, $index);
    }

    /**
     * @see interfaces\Collection::select()
     */
    public function select(callable $callback) : interfaces\Collection
    {
        return new static(array_filter($this->items, $callback));
    }

    /**
     * @see interfaces\Collection::reject()
     *
     * Usage of self::select() with the comparison in your callback inverted is preferred as this method is
     * somewhat slower than simply running array_filter.
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
     * @see interfaces\Collection::map()
     */
    public function map(callable $callback) : interfaces\Collection
    {
        return new static(array_map($callback, $this->items, array_keys($this->items)));
    }

    /**
     * @see interfaces\Collection::each()
     */
    public function each(callable $callback) : self
    {
        array_map($callback, $this->items);

        return $this;
    }

    /**
     * @see interfaces\Collection::reduce()
     */
    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->items, $callback, $initial);
    }

    /**
     * @see interfaces\Collection::implode()
     */
    public function implode($value, $glue = '') : string
    {
        return implode($glue, $this->pluck($value));
    }

    /**
     * @see interfaces\Collection::reverse()
     */
    public function reverse() : interfaces\Collection
    {
        return new static(array_reverse($this->items));
    }

    /**
     * @see interfaces\Collection::collapse()
     */
    public function collapse() : interfaces\Collection
    {
        return new static(utils\Arr::collapse($this->items));
    }

    /**
     * @see interfaces\Collection::flatten()
     */
    public function flatten() : interfaces\Collection
    {
        return new static(utils\Arr::flatten($this->items));
    }

    /**
     * @see interfaces\Collection::fetch()
     */
    public function fetch($name) : interfaces\Collection
    {
        return new static(utils\Arr::fetch($this->items, $name));
    }

    /**
     * @see interfaces\Collection::merge()
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
     * @see interfaces\Collection::diff()
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
     * Sorts this Collection using the given callable.
     *
     * @param   callable    $callback
     * @return  $this
     */
    public function sort(callable $callback) : self
    {
        uasort($this->items, $callback);

        return $this;
    }

    /**
     * @see interfaces\Collection::isEmpty()
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
     * @see core\interfaces\Arrayble::toArray()
     */
    public function toArray() : array
    {
        return array_map(function ($value) {
            return $value instanceof core\interfaces\Arrayable ? $value->toArray() : $value;

        }, $this->items);
    }

    /**
     * Magic alias for {@see self::get()}.
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Magic alias for {@see self::has()}.
     */
    public function __isset($key) : bool
    {
        return $this->has($key);
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
