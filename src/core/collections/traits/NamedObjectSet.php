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
 * Important note: For performance reasons the Collection maintains a name -> object map internally,
 * where the name is the object's name at the time it gets added to the Collection. This means that if
 * the object's name changes afterwards, the object will only be accessible via its originally set name,
 * unless explicitly removed and re-added (@potential).
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
trait NamedObjectSet
{
    /**
     * The traits of a NamedObjectSet trait.
     */
    use Collection;

    /**
     * @var bool    Whether the expected type should be automatically determined from the first item
     *              being added to the Set. Setting this to false when $collectedType is not set either
     *              effectively means that type invariance stops being guaranteed. Changing this value has
     *              no effect after the first item gets added to the Set.
     */
    protected $determineCollectedType = true;

    /**
     * @var string  The expected type of the objects in the Set. Only mutable when the Set is empty.
     */
    private $collectedType = '';

    /**
     * @see \nyx\core\collections\interfaces\NamedObjectSet::get()
     */
    public function get(string $name, core\interfaces\Named $default = null) : ?core\interfaces\Named
    {
        return $this->items[$name] ?? $default;
    }

    /**
     * @see \nyx\core\collections\interfaces\NamedObjectSet::add()
     */
    public function add(core\interfaces\Named $object) : interfaces\NamedObjectSet
    {
        $name = $object->getName();

        // Ensure the name is unique in the Set.
        if (isset($this->items[$name])) {
            throw new \OverflowException("An object with the name [$name] is already set in the Collection.");
        }

        // Should we use the class of this object as base type?
        if (!isset($this->collectedType) && $this->determineCollectedType) {
            $this->collectedType = get_class($object);
        }

        // If we are to check for a specific type, ensure the object is an instance of that.
        if (isset($this->collectedType) && !$object instanceof $this->collectedType) {
            throw new \InvalidArgumentException('Expected an instance of ['.$this->collectedType.'], got ['.diagnostics\Debug::getTypeName($object).'] instead.');
        }

        $this->items[$name] = $object;

        return $this;
    }

    /**
     * @see \nyx\core\collections\interfaces\NamedObjectSet::has()
     */
    public function has(string $name) : bool
    {
        return isset($this->items[$name]);
    }

    /**
     * @see \nyx\core\collections\interfaces\NamedObjectSet::contains()
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
     * @see \nyx\core\collections\interfaces\NamedObjectSet::remove()
     */
    public function remove(string $name) : interfaces\NamedObjectSet
    {
        unset($this->items[$name]);

        return $this;
    }

    /**
     * @see \nyx\core\collections\interfaces\Collection::replace()
     */
    public function replace($items) : interfaces\Collection
    {
        $this->items = [];

        foreach ($this->extractItems($items) as $item) {
            $this->add($item);
        }

        return $this;
    }

    /**
     * @see \nyx\core\collections\interfaces\NamedObjectSet::names()
     */
    public function names() : array
    {
        return array_keys($this->items);
    }

    /**
     * Returns the type being collected, as a fully qualified class name.
     *
     * @return  string  The base class of the objects being collected in this Set or an empty string if not set.
     */
    public function getCollectedType() : string
    {
        return $this->collectedType;
    }

    /**
     * Sets the type to be collected, as a fully qualified class name or an instance of the type (its class name
     * will be used in this case). When providing an object, the object must be an instance of core\interfaces\Named.
     *
     * @param   string|core\interfaces\Named    $type   The type to set.
     * @return  $this
     * @throws  \LogicException                         When trying to set the type for an already populated Set.
     * @throws  \InvalidArgumentException               When trying to set an unsupported type.
     */
    public function setCollectedType($type) : interfaces\NamedObjectSet
    {
        if (!empty($this->items)) {
            throw new \LogicException('The type being collected cannot be changed when the Set is populated.');
        }

        if (is_string($type)) {
            $this->collectedType = $type;
        } else if ($type instanceof core\interfaces\Named) {
            $this->collectedType = get_class($type);
        } else {
            throw new \InvalidArgumentException('Unsupported type given - expected class name or instance of '.interfaces\NamedObjectSet::class.', got ['.diagnostics\Debug::getTypeName($type).'] instead.');
        }

        return $this;
    }

    /**
     * @see \nyx\core\collections\interfaces\NamedObjectSet::get()
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @see \nyx\core\collections\interfaces\NamedObjectSet::add()
     */
    public function __set($name, $object)
    {
        throw new \LogicException('Objects in a NamedObjectSet cannot have keys defined by the user. Use add() instead of the magic __set().');
    }

    /**
     * @see \nyx\core\collections\interfaces\NamedObjectSet::has()
     */
    public function __isset($name) : bool
    {
        return $this->has($name);
    }

    /**
     * @see \nyx\core\collections\interfaces\NamedObjectSet::remove()
     */
    public function __unset($name)
    {
        return $this->remove($name);
    }

    /**
     * @see \nyx\core\collections\interfaces\NamedObjectSet::get()
     */
    public function offsetGet($name)
    {
        return $this->get($name);
    }

    /**
     * @see \nyx\core\collections\interfaces\NamedObjectSet::add()
     */
    public function offsetSet($name, $object)
    {
        if (isset($name)) {
            throw new \InvalidArgumentException('Objects in a NamedObjectSet cannot have keys defined by the user.');
        }

        return $this->add($object);
    }

    /**
     * @see \nyx\core\collections\interfaces\NamedObjectSet::has()
     */
    public function offsetExists($name)
    {
        return $this->has($name);
    }

    /**
     * @see \nyx\core\collections\interfaces\NamedObjectSet::remove()
     */
    public function offsetUnset($name)
    {
        return $this->remove($name);
    }
}
