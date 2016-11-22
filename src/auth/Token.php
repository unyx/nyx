<?php namespace nyx\auth;

/**
 * Token
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Token implements interfaces\Token
{
    /**
     * @var string  The Token's identifier.
     */
    protected $id;

    /**
     * Creates a new Token instance.
     *
     * @param   string  $id     The Token's identifier.
     */
    public function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * {@inheritDoc}
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function matches(interfaces\Token $that) : bool
    {
        return $this->id === $that->getId();
    }

    /**
     * {@inheritDoc}
     */
    public function serialize() : string
    {
        return serialize($this->id);
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($data)
    {
        $this->id = unserialize($data);
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function toJson(int $options = 0) : string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * {@inheritDoc}
     */
    public function toString() : string
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString() : string
    {
        return $this->toString();
    }

    /**
     * {@inheritDoc}
     */
    public function toArray() : array
    {
        return [
            'id' => $this->id
        ];
    }
}
