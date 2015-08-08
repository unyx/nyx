<?php namespace nyx\core\interfaces;

/**
 * Arrayable Interface
 *
 * An Arrayable object is one that provides a method to cast it to an array.
 *
 * @package     Nyx\Core
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/core/index.html
 */
interface Arrayable
{
    /**
     * Returns the object as an array.
     *
     * @return  array
     */
    public function toArray() : array;
}
