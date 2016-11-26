<?php namespace nyx\auth\signers;

// Internal dependencies
use nyx\auth;

/**
 * HMAC Signer
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
abstract class Hmac extends auth\Signer
{
    /**
     * {@inheritDoc}
     */
    const METHOD = 'hmac';

    /**
     * {@inheritDoc}
     */
    public function sign(string $payload, $key) : string
    {
        if ($key instanceof auth\interfaces\Credentials) {
            $key = $key->getSecret();
        }

        // At this point we expect to have a non-empty string as the key.
        if (!is_string($key) || empty($key)) {
            throw new \InvalidArgumentException('Invalid signing key provided.');
        }

        return hash_hmac($this->getAlgorithm(), $payload, $key, true);
    }

    /**
     * {@inheritDoc}
     */
    public function verify(string $expected, string $payload, $key) : bool
    {
        return hash_equals($expected, $this->sign($payload, $key));
    }
}
