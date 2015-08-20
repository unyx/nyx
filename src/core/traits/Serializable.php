<?php namespace nyx\core\traits;

/**
 * Serializable
 *
 * A Serializable object within Nyx is one that can be cast to an array, string, JSON string and serialized.
 *
 * This trait allows for the implementation of the core\interfaces\Serializable interface *if* both a toArray() and
 * a unserialize() method get implemented as well.
 *
 * @package     Nyx\Core
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/core/index.html
 */
trait Serializable
{
    /**
     * @see \Serializable::unserialize()
     */
    abstract public function unserialize($data);

    /**
     * @see core\interfaces\Arrayble::toArray()
     */
    abstract public function toArray() : array;

    /**
     * @see \Serializable::serialize()
     */
    public function serialize() : string
    {
        return serialize($this->toArray());
    }

    /**
     * @see \JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @see core\interfaces\Jsonable::toJson()
     */
    public function toJson(int $options = 0) : string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * @see core\interfaces\Stringable::toString()
     */
    public function toString() : string
    {
        return $this->toJson();
    }

    /**
     * Magic alias for {@see self::toString()}.
     */
    public function __toString()
    {
        return $this->toString();
    }
}
