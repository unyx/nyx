<?php namespace nyx\core\collections;

/**
 * Collection
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
abstract class Collection implements \IteratorAggregate, interfaces\Collection
{
    /**
     * Constructs a new Collection.
     *
     * @param   mixed   $items  An object implementing either the interfaces\Collection interface or the
     *                          core\interfaces\Arrayable interface, or any other type which will be cast
     *                          to an array.
     */
    public function __construct($items = null)
    {
        if (isset($items)) {
            $this->replace($items);
        }
    }
}
