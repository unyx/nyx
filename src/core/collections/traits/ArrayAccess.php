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
     * @see \ArrayAccess::offsetExists()
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * @see \ArrayAccess::offsetGet()
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * @see \ArrayAccess::offsetSet()
     */
    public function offsetSet($key, $value)
    {
        return $this->set($key, $value);
    }

    /**
     * @see \ArrayAccess::offsetUnset()
     */
    public function offsetUnset($key)
    {
        return $this->remove($key);
    }
}
