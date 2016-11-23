<?php namespace nyx\auth\id\protocols\oauth1\interfaces;

// External dependencies
use Psr\Http\Message\RequestInterface as Request;

// Internal dependencies
use nyx\auth;

/**
 * OAuth 1.0a Request Signer Interface
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
     * The supported signature methods defined by the OAuth v1.0 protocol.
     */
    const METHOD_PLAINTEXT = 'PLAINTEXT';
    const METHOD_HMAC_SHA1 = 'HMAC-SHA1';
    const METHOD_RSA_SHA1  = 'RSA-SHA1';

    /**
     * Returns the OAuth signature method.
     *
     * @return  string
     */
    public function getSignatureMethod() : string;

    /**
     * Generates a signature of the given Request, with the given authorization parameters,
     * using the provided Client and Token Credentials.
     *
     * @param   Request                         $request    The request for which to create the signature.
     * @param   array                           $params     The authorization parameters (OAuth 1.0 protocol parameters).
     * @param   auth\id\credentials\Client      $client     The Client's Credentials.
     * @param   auth\interfaces\Credentials     $token      The token Credentials.
     * @return  string                                      The signature of the Request/Credentials combination.
     */
    public function sign(Request $request, array $params, auth\id\credentials\Client $client, auth\interfaces\Credentials $token = null) : string;
}
