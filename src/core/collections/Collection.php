<?php namespace nyx\core\collections;

/**
 * Collection
 *
 * @package     Nyx\Core\Collections
 * @version     0.0.5
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/core/collections.html
 */
abstract class Collection implements \IteratorAggregate, interfaces\Collection
{
    /**
     * Constructs a new Collection.
     *
     * @param   mixed   $items   An object implementing either the interfaces\Collection interface or the
     *                           core\interfaces\Arrayable interface, or any other type which will be cast
     *                           to an array.
     */
    public function __construct($items = null)
    {
        // The "items" property inherited from the Collection trait is an array by default, so we only need
        // to set its value if it's actually given and if it is, the replace method will handle casting it to
        // a useful type or extracting data out of an already existing Collection.
        null !== $items && $this->replace($items);
    }
}
