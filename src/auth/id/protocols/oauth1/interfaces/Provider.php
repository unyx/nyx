<?php namespace nyx\auth\id\protocols\oauth1\interfaces;

// External dependencies
use GuzzleHttp\Promise\PromiseInterface as Promise;

// Internal dependencies
use nyx\auth\id\protocols\oauth1;
use nyx\auth;

/**
 * OAuth 1.0a Provider Interface
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Provider extends auth\id\interfaces\Provider
{
    /**
     * The URL at which temporary authorization credentials (tokens) can be requested from the Provider.
     */
    const URL_REQUEST = null;

    /**
     * Returns the temporary credentials acquisition URL of the Provider.
     *
     * @return  string
     */
    public function getTemporaryCredentialsUrl() : string;

    /**
     * Performs the initial Request to the Provider necessary to acquisition a set of temporary Credentials
     * which can subsequently be used to ask an entity to authorize said temporary Credentials, and which
     * then in turn can be exchanged for a proper set of OAuth 1.0a Credentials.
     *
     * @return  Promise     A Promise for a set of temporary OAuth 1.0a Credentials (an auth\Credentials instance).
     */
    public function handshake() : Promise;

    /**
     * Performs an exchange Request to the Provider, exchanging the temporary Credentials received during the
     * handshake along with a verifying string for a proper set of OAuth 1.0a Credentials.
     *
     * @param   auth\Credentials    $credentials    The temporary Credentials received during the handshake.
     * @param   string              $verifier       The verifying string received during the handshake.
     * @return  Promise                             A Promise for a set of valid OAuth 1.0a Credentials
     *                                              (an auth\Credentials instance).
     */
    public function exchange(auth\Credentials $credentials, string $verifier) : Promise;

    /**
     * Performs an identify Request to the Provider, returning information about the entity (the Identity) whose
     * OAuth 1.0a Credentials are used to perform the Request.
     *
     * @param   auth\Credentials    $credentials    A set of valid OAuth 1.0a Credentials.
     * @return  Promise                             A Promise for the Identity (an oauth1\Identity instance) of the
     *                                              Credentials' owning entity.
     */
    public function identify(auth\Credentials $credentials) : Promise;
}
