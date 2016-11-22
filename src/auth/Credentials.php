<?php namespace nyx\auth;

// External dependencies
use nyx\core;
use nyx\diagnostics;

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
     * @var string|interfaces\Credentials                   The secret associated with the underlying identifier.
     */
    protected $secret;

    /**
     * {@inheritDoc}
     *
     * @param   string|interfaces\Credentials   $secret     The secret associated with the underlying identifier.
     * @throws  \InvalidArgumentException                   When a secret of an invalid type is given.
     */
    public function __construct(string $id, $secret)
    {
        parent::__construct($id);

        if (!is_string($secret) && !$secret instanceof interfaces\Credentials) {
            throw new \InvalidArgumentException("The [secret] must be either a string or an instance of ".interfaces\Credentials::class.", got [".diagnostics\Debug::getTypeName($secret)."] instead.");
        }

        $this->secret = $secret;
    }

    /**
     * {@inheritDoc}
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * {@inheritDoc}
     */
    public function matches(interfaces\Token $that) : bool
    {
        // The interface assumes Token comparisons but we need data from our own, stricter interface.
        // @todo Loosen the Token interface to allow mixed types for the matches() method?
        if (!$that instanceof interfaces\Credentials) {
            return false;
        }

        // $that's secret will be required more than once so let's reduce calls.
        $otherSecret = $that->getSecret();

        // In the case of nested Credentials we are going to need to check for equality of the underlying data.
        // If only one of the secrets is a Credentials instance, then the control structure will proceed to the
        // next block and catch that with the identity comparison (returning false there).
        if ($this->secret instanceof interfaces\Credentials && $otherSecret instanceof interfaces\Credentials) {
            if (!$this->secret->matches($otherSecret)) {
                return false;
            }
        } elseif ($this->secret !== $otherSecret) {
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
