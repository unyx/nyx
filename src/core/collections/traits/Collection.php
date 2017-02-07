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
 * all of its in
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
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
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
        return $this->derive(array_slice($this->items, $offset, $length, $preserveKeys));
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
        return $this->derive(array_filter($this->items, $callback));
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
            if (!$callback($item)) {
                $result[$key] = $item;
            }
        }

        return $this->derive($result);
    }

    /**
     * @see \nyx\core\collections\interfaces\Collection::map()
     */
    public function map(callable $callback) : interfaces\Collection
    {
        return $this->derive(array_map($callback, $this->items, array_keys($this->items)));
    }

    /**
     * @see \nyx\core\collections\interfaces\Collection::each()
     */
    public function each(callable $callback) : interfaces\Collection
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
        return $this->derive(array_reverse($this->items));
    }

    /**
     * @see \nyx\core\collections\interfaces\Collection::collapse()
     */
    public function collapse() : interfaces\Collection
    {
        return $this->derive(utils\Arr::collapse($this->items));
    }

    /**
     * @see \nyx\core\collections\interfaces\Collection::flatten()
     */
    public function flatten() : interfaces\Collection
    {
        return $this->derive(utils\Arr::flatten($this->items));
    }

    /**
     * @see \nyx\core\collections\interfaces\Collection::fetch()
     */
    public function fetch($key) : interfaces\Collection
    {
        return $this->derive(utils\Arr::fetch($this->items, $key));
    }

    /**
     * @see \nyx\core\collections\interfaces\Collection::merge()
     */
    public function merge(...$with) : interfaces\Collection
    {
        $arrays = [$this->items];

        foreach ($with as $items) {
            $arrays[] = $this->extractItems($items);
        }

        return $this->derive(array_merge(...$arrays));
    }

    /**
     * @see \nyx\core\collections\interfaces\Collection::diff()
     */
    public function diff(...$against) : interfaces\Collection
    {
        $arrays = [$this->items];

        foreach ($against as $items) {
            $arrays[] = $this->extractItems($items);
        }

        return $this->derive(array_diff(...$arrays));
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
     * Creates a new instance based on this one, but populated by the given items.
     *
     * This can be useful as an override in child classes which take required parameters other than the items
     * in their constructor, allowing you to pass them in by overriding this method and thus more easily
     * retain the functionality of methods like map() or reduce() which return new instances of the Collection.
     *
     * @param   mixed   $items          The items to populate the new Collection with.
     * @return  interfaces\Collection
     */
    protected function derive($items) : interfaces\Collection
    {
        return new static($items);
    }

    /**
     * Inspects the given $items and attempts to resolve them to an iterable collection of items.
     *
     * @param   mixed   $items
     * @return  \iterable|array
     */
    protected function extractItems($items) : \iterable
    {
        // Catch arrays and Traversable objects.
        // By extension, this also includes other Collections (via their Iterator) *and* generators.
        // Keep especially the latter in mind when overriding concrete Collections.
        if (is_iterable($items)) {
            return $items;
        }

        // Arbitrary interfaces in order of preference.
        if ($items instanceof core\interfaces\Arrayable) {
            return $items->toArray();
        }

        if ($items instanceof \JsonSerializable) {
            return $items->jsonSerialize();
        }

        if ($items instanceof core\interfaces\Jsonable) {
            return json_decode($items->toJson(), true);
        }

        // Worst case scenario - use PHP's internals to attempt to cast it to an array.
        return (array) $items;
    }
}
