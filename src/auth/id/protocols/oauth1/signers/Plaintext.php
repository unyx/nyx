<?php namespace nyx\auth\id\protocols\oauth1\signers;

// External dependencies
use Psr\Http\Message\RequestInterface as Request;

// Internal dependencies
use nyx\auth\id\protocols\oauth1;
use nyx\auth;

/**
 * OAuth 1.0a PLAINTEXT Request Signer
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 * ----
 * @see         https://oauth.net/core/1.0a/#anchor21 (Spec #9.4 PLAINTEXT)
 */
class Plaintext extends oauth1\Signer
{
    /**
     * {@inheritDoc}
     */
    public function getMethod() : string
    {
        return oauth1\interfaces\Signer::METHOD_PLAINTEXT;
    }

    /**
     * {@inheritDoc}
     *
     * @see https://oauth.net/core/1.0a/#anchor22 (Spec #9.4.1 PLAINTEXT / Generating signature):
     * "(...) the concatenated encoded values of the Consumer Secret and Token Secret (...)
     *        The result MUST be encoded again."
     */
    public function sign(Request $request, array $params, auth\id\credentials\Client $client, auth\interfaces\Credentials $token = null) : string
    {
        return rawurlencode($this->createKey($client, $token));
    }
}
