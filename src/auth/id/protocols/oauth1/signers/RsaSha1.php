<?php namespace nyx\auth\id\protocols\oauth1\signers;

// External dependencies
use Psr\Http\Message\RequestInterface as Request;

// Internal dependencies
use nyx\auth\id\protocols\oauth1;
use nyx\auth;

/**
 * OAuth 1.0a RSA-SHA1 Request Signer
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
    public function getMethod() : string
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
        // Base64 encode the generated base string signed using the RSA-SHA1 method using our client secret,
        // eg. its private key.
        return base64_encode($this->getGenerator()->sign($this->buildBaseString($request, $params), $client));
    }

    /**
     * Returns the base signature generator used by this Request Signer.
     *
     * @return  auth\interfaces\Signer
     */
    protected function getGenerator() : auth\interfaces\Signer
    {
        static $generator;

        return $generator ?? $generator = new auth\signers\rsa\Sha1;
    }
}
