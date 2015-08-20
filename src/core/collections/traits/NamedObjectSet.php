<?php namespace nyx\core\collections\traits;

// External dependencies
use nyx\diagnostics;

// Internal dependencies
use nyx\core\collections\interfaces;
use nyx\core;

/**
 * Named Object Set Trait
 *
 * Allows for the implementation of the collections\interfaces\NamedObjectSet interface.
 *
 * @package     Nyx\Core\Collections
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/core/collections.html
 */
trait NamedObjectSet
{
    /**
     * The traits of a NamedObjectSet trait.
     */
    use Collection;

    /**
     * @see string  The expected type of the objects in the Set.
     */
    protected $collectedType;

    /**
     * @see interfaces\Sequence::get()
     */
    public function get(string $name, $default = null) : core\interfaces\Named
    {
        return $this->items[$name] ?? $default;
    }

    /**
     * @see interfaces\NamedObjectSet::add()
     *
     * @throws  \InvalidArgumentException   When expecting a specific type and the Object given is not an instance of it.
     */
    public function add(core\interfaces\Named $object) : self
    {
        $name = $object->getName();

        // Ensure the name is unique in the Set.
        if (isset($this->items[$name])) {
            throw new \OverflowException("An object with the name [$name] is already set in the Collection.");
        }

        // If we are to check for a specific type, ensure the object is an instance of that.
        if (null !== $this->collectedType && !($object instanceof $this->collectedType)) {
            throw new \InvalidArgumentException("Expected an instance of [$this->collectedType], got [".diagnostics\Debug::getTypeName($object)."] instead.");
        }

        $this->items[$name] = $object;

        return $this;
    }

    /**
     * @see interfaces\NamedObjectSet::has()
     */
    public function has(string $name) : bool
    {
        return isset($this->items[$name]);
    }

    /**
     * @see interfaces\NamedObjectSet::contains()
     */
    public function contains(core\interfaces\Named $object) : bool
    {
        $name = $object->getName();

        if (isset($this->items[$name])) {
            return $this->items[$name] === $object;
        }

        return false;
    }

    /**
     * @see interfaces\NamedObjectSet::remove()
     */
    public function remove(string $name) : self
    {
        unset($this->items[$name]);

        return $this;
    }

    /**
     * @see interfaces\Collection::replace()
     */
    public function replace($items) : self
    {
        $this->items = [];

        foreach ($this->extractItems($items) as $item) {
            $this->add($item);
        }

        return $this;
    }

    /**
     * @see interfaces\NamedObjectSet::names()
     */
    public function names() : array
    {
        return array_keys($this->items);
    }

    /**
     * Magic alias for {@see self::get()}.
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Magic alias for {@see self::set()}.
     */
    public function __set($name, $object)
    {
        throw new \LogicException("Objects in a NamedObjectSet cannot have keys defined by the user. Use add() instead of the magic __set().");
    }

    /**
     * Magic alias for {@see self::has()}.
     */
    public function __isset($name) : bool
    {
        return $this->has($name);
    }

    /**
     * Magic alias for {@see self::has()}.
     */
    public function __unset($name)
    {
        return $this->remove($name);
    }

    /**
     * @see self::get()
     */
    public function offsetGet($name)
    {
        return $this->get($name);
    }

    /**
     * @see self::add()
     */
    public function offsetSet($name, $object)
    {
        if (null !== $name) {
            throw new \InvalidArgumentException("Objects in a NamedObjectSet cannot have keys defined by the user.");
        }

        return $this->add($object);
    }

    /**
     * @see self::has()
     */
    public function offsetExists($name)
    {
        return $this->has($name);
    }

    /**
     * @see self::remove()
     */
    public function offsetUnset($name)
    {
        return $this->remove($name);
    }
}
