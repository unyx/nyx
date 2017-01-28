<?php namespace nyx\core\interfaces;

/**
 * Named Interface
 *
 * A Named object is one that has a name which can be get and set.
 *
 * Implementations MUST ensure that the a non-empty name is set no later than before the first use of
 * the implementing object, ideally during construction.
 *
 * @package     Nyx\Core
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Named
{
    /**
     * Returns the name of this object.
     *
     * @return  string
     */
    public function getName() : string;

    /**
     * Sets the name of this object.
     *
     * @param   string  $name   The name to set.
     * @return  $this
     */
    public function setName(string $name) : Named;
}
