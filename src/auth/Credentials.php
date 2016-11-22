<?php namespace nyx\auth;

// External dependencies
use nyx\core;

/**
 * Credentials
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Credentials extends Token implements interfaces\Credentials
{
    /**
     * The traits of a Credentials instance.
     */
    use core\traits\Serializable;

    /**
     * @var string  The secret associated with the underlying identifier.
     */
    protected $secret;

    /**
     * {@inheritDoc}
     *
     * @param   string  $secret     The secret associated with the underlying identifier.
     */
    public function __construct(string $id, string $secret)
    {
        parent::__construct($id);

        $this->secret = $secret;
    }

    /**
     * {@inheritDoc}
     */
    public function getSecret() : ?string
    {
        return $this->secret;
    }

    /**
     * {@inheritDoc}
     */
    public function matches(interfaces\Token $that) : bool
    {
        if (!$that instanceof static) {
            return false;
        }

        if ($this->secret !== $that->secret) {
            return false;
        }

        return parent::matches($that);
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($data)
    {
        $data = unserialize($data);

        $this->id     = $data['id'];
        $this->secret = $data['secret'];
    }

    /**
     * {@inheritDoc}
     */
    public function toArray() : array
    {
        return [
            'id'     => $this->id,
            'secret' => $this->secret
        ];
    }
}
