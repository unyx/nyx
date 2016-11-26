<?php namespace nyx\auth\interfaces;

/**
 * Signer Interface
 *
 * Security note on Signers: The hashers in this component are *absolutely not* meant for hashing passwords.
 * They are used for generating signatures of messages, which in turn are used for verifying the authenticity
 * of those messages, but are not intended to be used outside of that purpose.
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Signer
{
    /**
     * Generates a signature of the payload using the provided key.
     *
     * @param   string              $payload    The payload to create a signature for.
     * @param   string|Credentials  $key        The key in form of a string or a Credentials instance. If a Credentials
     *                                          instance is given, its *secret* (private part) will be used.
     * @return  string                          The signature.
     */
    public function sign(string $payload, $key) : string;

    /**
     * Verifies that the signature of the payload generated using the provided key matches the expected signature.
     *
     * @param   string              $expected   The expected signature.
     * @param   $payload            $payload    The payload that should form the verified signature.
     * @param   string|Credentials  $key        The key in form of a string or a Credentials instance. If a Credentials
     *                                          instance is given, its *id* (public part) will be used.
     * @return  bool                            True when the signature could be verified, false otherwise.
     */
    public function verify(string $expected, string $payload, $key) : bool;

    /**
     * Returns the name/identifier of the hashing method this Signer uses.
     *
     * @return  string
     */
    public function getMethod() : string;

    /**
     * Returns the name/identifier of the hashing algorithm this Signer uses.
     *
     * @return  string
     */
    public function getAlgorithm() : string;
}
