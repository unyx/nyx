<?php namespace nyx\core\interfaces;

/**
 * Named Interface
 *
 * A Named object is one that has a name which can be get and set. Implementations MUST ensure that the a non-empty
 * name is set no later than before the first use of the inheriting object, ideally during construction.
 *
 * @package     Nyx\Core
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/core/index.html
 */
interface Named
{
    /**
     * Returns the name of the implementer of this interface.
     *
     * @return  string
     */
    public function getName() : string;

    /**
     * Sets the name of the implementer of this interface.
     *
     * @param   string  $name   The name to set.
     * @return  $this
     */
    public function setName(string $name);
}
