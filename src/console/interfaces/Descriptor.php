<?php namespace nyx\console\interfaces;

/**
 * Descriptor Interface
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Descriptor
{
    /**
     * Describes the given object.
     *
     * @param   object  $object             The object to describe.
     * @param   array   $options            Additional options to be considered by the Descriptor.
     * @return  mixed                       The description. The type returned depends on the implementation.
     * @throws  \InvalidArgumentException   When the given object cannot be described by the Descriptor.
     */
    public function describe($object, array $options = null);
}
