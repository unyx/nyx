<?php namespace nyx\auth\id\protocols\oauth1\signers;

// External dependencies
use Psr\Http\Message\RequestInterface as Request;

// Internal dependencies
use nyx\auth\id\protocols\oauth1;
use nyx\auth;

/**
 * OAuth 1.0a RSA-SHA1 Request Signer
 *
 * Note: Relies on the OpenSSL extension being available but will *not throw* on its own until a function
 * provided by the OpenSSL extension gets invoked.
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 * ----
 * @see         https://oauth.net/core/1.0a/#anchor18 (Spec #9.3 RSA-SHA1)
 */
class RsaSha1 extends oauth1\Signer
{
    /**
     * {@inheritDoc}
     */
    public function getSignatureMethod() : string
    {
        return oauth1\interfaces\Signer::METHOD_RSA_SHA1;
    }

    /**
     * {@inheritDoc}
     *
     * Note: The OAUth Token is entirely ignored in this signing flow, as per spec.
     */
    public function sign(Request $request, array $params, auth\id\credentials\Client $client, auth\interfaces\Credentials $token = null) : string
    {
        if (false === $key = $this->createKey($client)) {
            throw new \RuntimeException('Failed to open the private key (invalid passphrase?).');
        }

        // openssl_sign() populates this by reference so we need a holder value.
        $signature = '';
        $success = openssl_sign($this->buildBaseString($request, $params), $signature, $key, OPENSSL_ALGO_SHA1);

        // Free the resource in any case, before the potential throw.
        openssl_free_key($key);

        if (false === $success) {
            throw new \RuntimeException('Failed to sign the Request with the provided private key.');
        }

        return base64_encode($signature);
    }

    /**
     * {@inheritDoc}
     *
     * @return  resource|bool
     */
    protected function createKey(auth\id\credentials\Client $client, auth\interfaces\Credentials $token = null)
    {
        // If it's a nested Credentials instance, we assume the id is the key (either the full key or
        // a path starting with "file://", as understood by openssl_pkey_get_private(), and the secret is the
        // key's passphrase).
        if (($key = $client->getSecret()) instanceof auth\interfaces\Credentials) {
            return openssl_pkey_get_private($key->getId(), $key->getSecret());
        }

        return openssl_pkey_get_private($key);
    }
}
