<?php namespace nyx\core\interfaces;

/**
 * Stringable Interface
 *
 * A Stringable object is one that provides a method to cast it to a string. Note: Implementation of this method
 * only ensures that the object can be cast to a string without causing solar flares or other cosmic activity,
 * ie. you can be sure of the type the methods will return, but not necessarily the format of the string.
 *
 * The preferred method to use is the explicit {@see self::toString()}. Implementations should alias the implicit,
 * magic method to its explicit counterpart, so that overriding only requires the modification of one method.
 *
 * @package     Nyx\Core\Interfaces
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/core/index.html
 */
interface Stringable
{
    /**
     * Returns the string representation of the object.
     *
     * @return  string
     */
    public function toString() : string;

    /**
     * {@see self::toString()}
     */
    public function __toString() : string;
}
