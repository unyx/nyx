<?php namespace nyx\core\collections\interfaces;

// Internal dependencies
use nyx\core;

/**
 * Named Object Set Interface
 *
 * Type invariance of Objects in the Set may be optionally guaranteed by implementations but is not enforced
 * by the interface. Neither is their uniqueness other than by their name (key).
 *
 * The default implementation in Nyx (\nyx\core\collections\NamedObjectSet) guarantees both by default, however.
 *
 * @package     Nyx\Core\Collections
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/core/collections.html
 */
interface NamedObjectSet extends Collection
{
    /**
     * Returns an item identified by its name.
     *
     * @param   string  $name           The name of the Object to return.
     * @param   mixed   $default        The default value to return when the given item does not exist in
     *                                  the Set.
     * @return  core\interfaces\Named   The object or the default value given if the item couldn't be found.
     */
    public function get(string $name, $default = null) : core\interfaces\Named;

    /**
     * Adds the given Named Object to the Set.
     *
     * @param   core\interfaces\Named   $item   The Named Object to add.
     * @return  $this
     * @throws  \OverflowException              When an Object with the same name is already set.
     * @throws  \InvalidArgumentException       When expecting a specific type and the Object given is not an
     *                                          instance of it.
     */
    public function add(core\interfaces\Named $object) : self;

    /**
     * Checks whether the given item identified by its name exists in the Set.
     *
     * @param   string  $name   The name of the item to check for.
     * @return  bool            True if the item exists in the Set, false otherwise.
     */
    public function has(string $name) : bool;

    /**
     * Checks whether the given object exists in the Set.
     *
     * @param   mixed   $object     The object to check for.
     * @return  bool                True if the item exists in the Set, false otherwise.
     */
    public function contains(core\interfaces\Named $object) : bool;

    /**
     * Removes an item from the Set by its name.
     *
     * @param   string  $name   The name of the object to remove.
     * @return  $this
     */
    public function remove(string $name) : self;

    /**
     * Returns the names of the items in this Set indexed numerically. Acts similar to array_keys() except
     * for strict comparisons being enforced.
     *
     * Note: Compared to, for example, Map::keys() the results of this method cannot be limited to
     * the keys of given objects - in the case of a NamedObjectSet if you have the items beforehand, you
     * already have their names since they implement core\interfaces\Named.
     *
     * @return  array
     */
    public function names() : array;
}
