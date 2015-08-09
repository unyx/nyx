<?php namespace nyx\core\collections;

/**
 * Set
 *
 * @package     Nyx\Core\Collections
 * @version     0.0.5
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/core/collections.html
 */
class Set extends Collection implements \IteratorAggregate, interfaces\Set
{
    use traits\Set;
}
