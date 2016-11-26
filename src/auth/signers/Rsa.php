<?php namespace nyx\auth\signers;

// Internal dependencies
use nyx\auth;

/**
 * RSA Signer
 *
 * Note: Relies on the OpenSSL extension being available but will *not throw* on its own until a function
 * provided by the OpenSSL extension gets invoked.
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
abstract class Rsa extends auth\Signer
{
    /**
     * {@inheritDoc}
     */
    const METHOD = 'rsa';

    /**
     * {@inheritDoc}
     */
    public function sign(string $payload, $key) : string
    {
        // If a Credentials instance is given, we are going to use its *private* part.
        if ($key instanceof auth\interfaces\Credentials) {
            $key = $key->getSecret();
        }

        // Load the actual key as a resource.
        // Note: This is correct - the initial private key can itself be a Credentials instance where
        // the secret is its passphrase.
        if ($key instanceof auth\interfaces\Credentials) {
            $key = openssl_get_privatekey($key->getId(), $key->getSecret());
        } else {
            $key = openssl_get_privatekey($key);
        }

        $signature = '';
        $success   = openssl_sign($payload, $signature, $this->validateKey($key), $this->getAlgorithm());

        // Free the resource in any case, before the potential throw.
        openssl_free_key($key);

        if (false === $success) {
            throw new \RuntimeException('Failed to sign the payload. Reason: '.openssl_error_string());
        }

        return $signature;
    }

    /**
     * {@inheritDoc}
     */
    public function verify(string $expected, string $payload, $key) : bool
    {
        // If a Credentials instance is given, we are going to use its *public* part.
        if ($key instanceof auth\interfaces\Credentials) {
            $key = $key->getId();
        }

        // Load the key.
        $key     = openssl_get_publickey($key);
        $success = openssl_verify($payload, $expected, $this->validateKey($key), $this->getAlgorithm());

        // Free the resource in any case, before the potential throw.
        openssl_free_key($key);

        if (-1 === $success) {
            throw new \RuntimeException('An error occurred while verifying the signature: '.openssl_error_string());
        }

        // $success will be 1 if the signatures matched, 0 otherwise.
        return 1 === $success;
    }

    /**
     * Validates the given key resource (as opened by one of the openssl_get_*key functions).
     *
     * @param   resource    $key
     * @return  resource
     * @throws  \InvalidArgumentException   When the key is not a valid resource or is not an RSA key.
     */
    protected function validateKey($key)
    {
        // First, ensure the respective get_*key method did not return false, meaning we got a valid
        // resource pointer at hand.
        if (false === $key) {
            throw new \InvalidArgumentException('Failed to parse the RSA key. Reason: '.openssl_error_string());
        }

        // We need to make there is actually a valid RSA key in that resource.
        $details = openssl_pkey_get_details($key);

        if (!isset($details['key']) || $details['type'] !== OPENSSL_KEYTYPE_RSA) {
            throw new \InvalidArgumentException('This key does not appear to be a valid RSA key.');
        }

        return $key;
    }
}
