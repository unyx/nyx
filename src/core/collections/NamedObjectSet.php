<?php namespace nyx\core\collections;

/**
 * Named Object Set
 *
 * @package     Nyx\Core\Collections
 * @version     0.0.5
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/core/collections.html
 */
class NamedObjectSet extends Collection implements \IteratorAggregate, interfaces\NamedObjectSet
{
    use traits\NamedObjectSet;
}
