<?php namespace nyx\core\collections\traits;

// Internal dependencies
use nyx\core\collections\interfaces;

/**
 * Sequence
 *
 * Allows for the implementation of the collections\interfaces\Sequence interface.
 *
 * @package     Nyx\Console\Application
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/core/collections.html
 */
trait Sequence
{
    /**
     * The traits of a Sequence trait.
     */
    use Collection;

    /**
     * @see interfaces\Sequence::get()
     */
    public function get(int $index, $default = null)
    {
        return $this->items[$index] ?? $default;
    }

    /**
     * @see interfaces\Sequence::push()
     */
    public function push($item) : self
    {
        if (null === $item) {
            throw new \InvalidArgumentException("Items in a Sequence cannot have a value of null.");
        }

        $this->items[] = $item;

        return $this;
    }

    /**
     * @see interfaces\Sequence::prepend()
     */
    public function prepend($item) : self
    {
        if (null === $item) {
            throw new \InvalidArgumentException("Items in a Sequence cannot have a value of null.");
        }

        array_unshift($this->items, $value);

        return $this;
    }

    /**
     * @see interfaces\Sequence::update()
     */
    public function update(int $index, $item) : self
    {
        if (null === $item) {
            throw new \InvalidArgumentException("Items in a Sequence cannot have a value of null.");
        }

        if (!isset($this->items[$index])) {
            throw new \OutOfBoundsException("Cannot update - no item at given index [$index].");
        }

        $this->items[$index] = $item;

        return $this;
    }

    /**
     * @see interfaces\Sequence::has()
     */
    public function has(int $index) : bool
    {
        return isset($this->items[$index]);
    }

    /**
     * @see interfaces\Sequence::contains()
     */
    public function contains($item) : bool
    {
        return empty($this->items) ? false : (-1 !== $this->indexOf($item));
    }

    /**
     * @see interfaces\Sequence::remove()
     */
    public function remove(int $index) : self
    {
        // Note: We need to maintain order so we do actually need to check whether we remove
        // an item or whether it's already gone.
        if (!isset($this->items[$index])) {
            return $this;
        }

        unset($this->items[$index]);

        // Re-order the items.
        $this->items = array_values($this->items);

        return $this;
    }

    /**
     * @see interfaces\Sequence::shift()
     */
    public function shift()
    {
        return array_shift($this->items);
    }

    /**
     * @see interfaces\Sequence::pop()
     */
    public function pop()
    {
        return array_pop($this->items);
    }

    /**
     * @see interfaces\Sequence::indexOf()
     *
     * Note:
     */
    public function indexOf($item) : int
    {
        foreach ($this->items as $key => $value) {
            if ($item === $value) {
                return $key;
            }
        }

        return -1;
    }

    /**
     * @see interfaces\Sequence::lastIndexOf()
     */
    public function lastIndexOf($item) : int
    {
        // Return early if there's nothing in the array. We need the count anyways
        // so might as well use it without adding notable overhead.
        if (!$count = count($this->items)) {
            return -1;
        }

        // Simply counting down is faster than iterating over array_reverse().
        for ($i = $count - 1; $i >= 0; $i--) {
            if ($this->items[$i] === $item) {
                return $i;
            }
        }

        return -1;
    }

    /**
     * @see interfaces\Collection::replace()
     */
    public function replace($items) : self
    {
        $this->items = [];

        foreach ($this->extractItems($items) as $item) {
            $this->push($item);
        }

        return $this;
    }

    /**
     * @see interfaces\Sequence::indices()
     */
    public function indices($of = null) : array
    {
        return array_keys($this->items, $of, true);
    }

    /**
     * Magic alias for {@see self::get()}.
     */
    public function __get($index)
    {
        return $this->get($index);
    }

    /**
     * Magic alias for {@see self::update()}.
     */
    public function __set($index, $item)
    {
        $this->update($index, $item);
    }

    /**
     * Magic alias for {@see self::has()}.
     */
    public function __isset($index) : bool
    {
        return $this->has($index);
    }

    /**
     * Magic alias for {@see self::has()}.
     */
    public function __unset($index)
    {
        return $this->remove($index);
    }

    /**
     * @see self::get()
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * @see self::push()
     * @see self::update()
     */
    public function offsetSet($index, $item)
    {
        if (null === $index) {
            return $this->push($item);
        }

        return $this->update($index, $item);
    }

    /**
     * @see self::has()
     */
    public function offsetExists($index)
    {
        return $this->has($index);
    }

    /**
     * @see self::remove()
     */
    public function offsetUnset($index)
    {
        return $this->remove($index);
    }
}
