<?php namespace nyx\auth\id\protocols\oauth1;

// External dependencies
use Psr\Http\Message\RequestInterface as Request;

// Internal dependencies
use nyx\auth\id\credentials;
use nyx\auth;

/**
 * Base OAuth 1,0a Request Signer
 *
 * Note: Methods for building the base string for the signature are included in this base class despite
 *       the PLAINTEXT Signer not using them (as per spec). This is merely to avoid offloading them onto a trait and
 *       having to load an additional file (containing the trait's code) for the HMAC-SHA1 and RSA-SHA1 signers
 *       (which both utilize the base string), since use of PLAINTEXT signing is discouraged altogether,
 *       unless the connection itself is verified to be secure.
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
abstract class Signer implements interfaces\Signer
{
    /**
     * Generates the signature's base string for the given Request.
     *
     * @param   Request     $request    The Request to generate the base string for.
     * @param   array       $params     Additional protocol parameters.
     * @return  string
     * ----
     * @see     https://tools.ietf.org/html/rfc5849#section-3.4.1
     * @see     https://tools.ietf.org/html/rfc5849#section-3.4.1.3
     */
    protected function buildBaseString(Request $request, array $params) : string
    {
        return $this->formMethod($request).'&'.$this->formUri($request).'&'.$this->formQueryString($request, $params);
    }

    /**
     * Forms the Request's method according to spec.
     *
     * @param   Request $request
     * @return  string
     */
    protected function formMethod(Request $request) : string
    {
        return strtoupper($request->getMethod());
    }

    /**
     * Forms the Request's URI according to spec.
     *
     * @param   Request $request
     * @return  string
     */
    protected function formUri(Request $request) : string
    {
        // Remove query params from the URL (Spec: #9.1.2.).
        return rawurlencode($request->getUri()->withQuery(''));
    }

    /**
     * Forms the Request's query string according to spec.
     *
     * @param   Request $request
     * @param   array   $params
     * @return  string
     */
    protected function formQueryString(Request $request, array $params) : string
    {
        // The 'realm', if present, is excluded from the protocol parameters when generating the base string.
        unset($params['realm']);

        // Form parameters, if present in the body, need to be included in query string portion of the base string.
        // Note: We are assuming the Request has a proper content-type set, not actually checking the body!
        if ($request->getHeaderLine('Content-Type') === 'application/x-www-form-urlencoded') {
            $params += \GuzzleHttp\Psr7\parse_query($request->getBody()->getContents());
        }

        // Initial query string parameters also need to be included in the base string and properly encoded.
        $params += \GuzzleHttp\Psr7\parse_query($request->getUri()->getQuery());

        return $this->buildQueryStringFromParams($this->normalizeParameters($params));
    }

    /**
     * Encodes and sorts the protocol parameters according to spec.
     *
     * @param   array   $params
     * @return  array
     */
    protected function normalizeParameters(array $params) : array
    {
        // Recursively percent encode each key/value pair in the params.
        array_walk_recursive($params, function (&$key, &$value) {
            $key   = rawurlencode(rawurldecode($key));
            $value = rawurlencode(rawurldecode($value));
        });

        // Sort the keys lexicographically (alphabetically in PHP's case).
        ksort($params);

        return $params;
    }

    /**
     * Creates a to-spec encoded query string out of each key/value pair in the initial array.
     * Handles multi-dimensional arrays recursively.
     *
     * @param   array   $params         Array of parameters to convert.
     * @param   array   $queryParams    The parameters to extend (used internally for nested data).
     * @param   string  $prevKey        Optional Array key to append
     * @return  string
     */
    protected function buildQueryStringFromParams(array $params, array $queryParams = null, string $prevKey = '') : string
    {
        if ($initial = !isset($queryParams)) {
            $queryParams = [];
        }

        foreach ($params as $key => $value) {
            // When nested...
            if ($prevKey) {
                $key = $prevKey.'['.$key.']';
            }

            if (is_array($value)) {
                $queryParams = $this->buildQueryStringFromParams($value, $queryParams, $key);
            } else {
                $queryParams[] = rawurlencode($key.'='.$value);
            }
        }

        if ($initial) {
            return implode('%26', $queryParams); // Ampersand.
        }

        return $queryParams;
    }

    /**
     * Creates a to-spec encoded signing key based on the Client's Credentials (and Token Credentials, if provided).
     *
     * @param   credentials\Client          $client
     * @param   auth\interfaces\Credentials $token
     * @return  mixed
     */
    protected function createKey(credentials\Client $client, auth\interfaces\Credentials $token = null)
    {
        // The joining ampersand after the encoded Client's secret is correctly left in even if no Token is being
        // included in the key.
        return rawurlencode($client->getSecret()) . '&' . (isset($token) ? rawurlencode($token->getSecret()) : '');
    }
}
