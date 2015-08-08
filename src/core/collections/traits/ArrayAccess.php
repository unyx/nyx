<?php namespace nyx\core\collections\traits;

/**
 * ArrayAccess
 *
 * A helper-like trait for objects that contain Map-like methods (ie. has, get, set, remove) to be able to
 * easily implement the ArrayAccess interface.
 *
 * @package     Nyx\Core\Collections
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/core/collections.html
 */
trait ArrayAccess
{
    /**
     * Checks whether the given item identified by its key exists in the object.
     *
     * @param   string|int  $key    The key of the item to check for.
     * @return  bool                True if the item exists in the object, false otherwise.
     */
    abstract function has($key) : bool;

    /**
     * Returns an item identified by its key.
     *
     * @param   string|int  $key        The key of the item to return.
     * @param   mixed       $default    The default value to return when the given item does not exist in
     *                                  the Collection.
     * @return  mixed                   The item or the default value given if the item couldn't be found.
     */
    abstract function get($key, $default = null);

    /**
     * Sets the given value in the object.
     *
     * @param   string|int  $key    The key the item should be set as.
     * @param   mixed       $item   The item to set.
     * @return  $this
     */
    abstract function set($key, $value) : self;

    /**
     * Removes the item identified by $key from the object.
     *
     * @param   string|int  $key    The key of the item to remove.
     * @return  $this
     */
    abstract function remove($key) : self;

    /**
     * @see self::has()
     */
    public function offsetExists($key)
    {
        return $this->has($key);
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
    public function offsetSet($key, $value)
    {
        return $this->set($key, $value);
    }

    /**
     * @see self::remove()
     */
    public function offsetUnset($key)
    {
        return $this->remove($key);
    }
}
