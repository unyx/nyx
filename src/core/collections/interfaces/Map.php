<?php namespace nyx\core\collections\interfaces;

/**
 * Map Interface
 *
 * @package     Nyx\Core\Collections
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/core/collections.html
 */
interface Map extends Collection
{
    /**
     * Returns an item identified by its key.
     *
     * @param   string|int  $key        The key of the item to return.
     * @param   mixed       $default    The default value to return when the given item does not exist in
     *                                  the Map.
     * @return  mixed                   The item or the default value given if the item couldn't be found.
     */
    public function get($key, $default = null);

    /**
     * Sets the given item in the Collection.
     *
     * @param   string|int  $key    The key the item should be set as.
     * @param   mixed       $item   The item to set.
     * @return  $this
     */
    public function set($key, $item) : self;

    /**
     * Checks whether the given item identified by its key exists in the Map.
     *
     * @param   string|int  $key    The key of the item to check for.
     * @return  bool                True if the item exists in the Collection, false otherwise.
     */
    public function has($key) : bool;

    /**
     * Checks whether the given item identified by its value exists in the Map.
     *
     * @param   mixed   $item       The value of the item to check for.
     * @return  bool                True if the item exists in the Map, false otherwise.
     */
    public function contains($item) : bool;

    /**
     * Removes an item from the Map by its key.
     *
     * @param   string|int  $key        The key of the item to remove.
     * @return  $this
     */
    public function remove($key) : self;

    /**
     * Returns the key of the item identified by its value.
     *
     * @param   mixed       $item   The value of the item to check.
     * @return  string|int          The key of the item or null if it couldn't be found.
     */
    public function key($item);

    /**
     * Returns the keys of the items in this Map indexed numerically. Acts similar to array_keys() except
     * for strict comparisons being enforced.
     *
     * @param   mixed   $of         If given, only the keys of the given items (values) will be returned.
     * @return  array
     */
    public function keys($of = null) : array;

    /**
     * Returns all items contained in this Map indexed numerically, disregarding their keys.
     *
     * @return  array
     */
    public function values() : array;
}
