<?php namespace nyx\core\collections\interfaces;

/**
 * Set Interface
 *
 * @package     Nyx\Core\Collections
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/core/collections.html
 */
interface Set extends Collection
{
    /**
     * Sets the given item in the Collection.
     *
     * @param   mixed   $item   The item to set.
     * @return  $this
     */
    public function set($item) : self;
}
