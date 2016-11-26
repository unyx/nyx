<?php namespace nyx\auth\id\protocols\oauth1\middlewares;

// External dependencies
use Psr\Http\Message\RequestInterface as Request;
use nyx\utils;

// Internal dependencies
use nyx\auth\id\protocols\oauth1;
use nyx\auth;

/**
 * OAuth 1.0a Request Authorization Middleware
 *
 * Adds OAuth 1.0a protocol specific parameters to the Request, including its signature. Should be used last
 * in a middleware stack as the signature is only generated and valid for the request parameters present at the
 * time of its creation.
 *
 * To invoke this middleware, when it is part of a middleware stack, the $options array passed along with
 * the Request must contain an 'oauth1' key that must not be null. It may be an array containing any
 * of those optional keys:
 *
 *  - 'signer': an instance of oauth1\interfaces\Signer used to create the Request's signature.
 *     Note: This field is *mandatory if* the Middleware gets constructed without a default Signer.
 *  - 'client': an instance of auth\id\credentials\Client containing the client's (consumer's) credentials.
 *     Note: This field is *mandatory if* the Middleware gets constructed without a set of default client credentials.
 *  - 'token': a auth\interfaces\Credentials instance representing the OAuth token;
 *  - 'callback': when true, a "oauth_callback" protocol parameter will be added to the Request,
 *     based on the Client Credentials given;
 *  - 'params': an array of additional protocol parameters which will be appended to the
 *     base protocol parameters (@see gatherAuthorizationParams()). Could be "realm" or other keys
 *     implemented by a particular Provider;
 *
 * All other keys are ignored by default.
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Authorization
{
    /**
     * @see http://oauth.net/core/1.0/#consumer_req_param
     *      Note: Passing the protocol parameters with the body of a Request is currently not implemented by this
     *      middleware, despite the spec allowing it.
     */
    const METHOD_HEADER = 'header';
    const METHOD_QUERY  = 'query';

    /**
     * @var auth\id\credentials\Client  The default Client Credentials to use when those are not passed with the options.
     */
    protected $client;

    /**
     * @var oauth1\interfaces\Signer    The default Signer to use when it is not passed with the options.
     */
    protected $signer;

    /**
     * @var string  The method with which the protocol parameters will be added to the Request being handled.
     */
    private $method = self::METHOD_HEADER;

    /**
     * Constructs a new OAuth 1.0a Request Authorization Middleware instance.
     *
     * Note: In a scenario where this handler is used universally in a HTTP Client handler stack that may communicate
     *       with several different OAuth 1.0a providers, to facilitate reuse of the instance and to avoid potential
     *       confusion in case of authorization errors, it is suggested to avoid using a default set of client
     *       credentials and a signer, and instead pass them in along with each Request's $options array.
     *
     * @param   auth\id\credentials\Client  $client     The default client credentials to sign requests with.
     * @param   oauth1\interfaces\Signer    $signer     The default Signer to generate the request's signature with.
     */
    public function __construct(auth\id\credentials\Client $client = null, oauth1\interfaces\Signer $signer = null)
    {
        $this->client = $client;
        $this->signer = $signer;
    }

    /**
     * Sets the method by which the protocol parameters will be added to the Request.
     *
     * @param   string  $method             One of the METHOD_* class constants.
     * @return  $this
     * @throws  \InvalidArgumentException   When attempting to set an unsupported method.
     */
    public function setMethod(string $method) : Authorization
    {
        switch($method) {
            case self::METHOD_HEADER:
            case self::METHOD_QUERY:
                $this->method = $method;
                return $this;
        }

        throw new \InvalidArgumentException("Authorization method [$method] is not supported by this middleware.");
    }

    /**
     * Invokes the middleware if the $options passed along the Request contain an 'oauth1' key.
     * See the class description for which kind of additional options are supported/mandatory.
     *
     * @param   callable    $handler
     * @return  callable
     */
    public function __invoke(callable $handler) : callable
    {
        return function ($request, array& $stackOptions) use ($handler) {

            // Skip to the next handler if we weren't asked to do any stuff.
            if (!isset($stackOptions['oauth1'])) {
                return $handler($request, $stackOptions);
            }

            // We'll be working with references internally and shifting some values around,
            // so we might just as well make all the parameters we gather and append more easily accessible
            // by pushing into the $stackOptions directly in case there actually *are* handlers in the stack
            // that have to do some post-processing after us.
            $handlerOptions =& $stackOptions['oauth1'] ?: $stackOptions['oauth1'] = [];

            $this->parseOptions($handlerOptions);
            $this->gatherAuthorizationParams($handlerOptions);
            $this->sign($request, $handlerOptions);

            // Invoke the next handler with our freshly authorized Request instance.
            return $handler($this->handle($request, $handlerOptions), $stackOptions);
        };
    }

    /**
     * Merges and populates the base protocol parameters with any optional protocol parameters passed in the options.
     *
     * @param   array            $options   A reference to the options passed along with the Request.
     * @throws  \InvalidArgumentException   When no default signer/credentials are available and no valid
     *                                      signer/credentials have been given along with the $options.
     * @throws  \InvalidArgumentException   When the 'token' key is given but is not a auth\interfaces\Credentials instance.
     */
    protected function parseOptions(array& $options)
    {
        // Ensure we got a valid 'signer' key.
        if ((null === $this->signer && !isset($options['signer'])) || (isset($options['signer']) && !$options['signer'] instanceof oauth1\interfaces\Signer)) {
            throw new \InvalidArgumentException('A [signer] key with a Signer implementing '.oauth1\interfaces\Signer::class.' must be provided.');
        }

        // Ensure the 'client' is set and contains valid Credentials.
        if ((null === $this->client && !isset($options['client'])) || (isset($options['client']) && !$options['client'] instanceof auth\id\credentials\Client)) {
            throw new \InvalidArgumentException('A [client] key with a Credentials implementing '.auth\id\credentials\Client::class.' must be provided.');
        }

        // Ensure the 'params' key is always set.
        if (!isset($options['params'])) {
            $options['params'] = [];
        }

        // If the 'token' optional key is present, we'll automatically push it's id into the authorization params
        // and use its secret in the signing process. Provided it's of the appropriate type.
        if (isset($options['token'])) {
            if (!$options['token'] instanceof auth\interfaces\Credentials) {
                throw new \InvalidArgumentException('The [token] key, if provided, must be an instance of '.auth\interfaces\Credentials::class.'.');
            }

            $options['params']['oauth_token'] = $options['token']->getId();
        }

        // If the 'callback' optional key is present and true, we'll set the oauth_callback authorization parameter
        // automatically, based on the consumer's redirect URI.
        if (isset($options['callback']) && true === $options['callback']) {
            $options['params']['oauth_callback'] = isset($options['client']) ? $options['client']->getRedirectUri() : $this->client->getRedirectUri();
        }
    }

    /**
     * Merges and populates the base protocol parameters with any optional protocol parameters passed in the options.
     *
     * @param   array   $options    A reference to the options passed along with the Request.
     */
    protected function gatherAuthorizationParams(array& $options)
    {
        // The signature must not be included in any base string - we'll generate the signature for the current
        // parameters in a moment anyways.
        // @see https://oauth.net/core/1.0/#anchor14 (Spec #9.1.1)
        unset($options['params']['oauth_signature']);

        // Unite our base parameters (which cannot be overridden) with the optional ones passed in.
        $options['params'] = [
            'oauth_version'          => '1.0',
            'oauth_consumer_key'     => isset($options['client']) ? $options['client']->getId()     : $this->client->getId(),
            'oauth_signature_method' => isset($options['signer']) ? $options['signer']->getMethod() : $this->signer->getMethod(),
            'oauth_nonce'            => utils\Random::string(6, utils\str\Character::CHARS_BASE64, utils\Random::STRENGTH_NONE),
            'oauth_timestamp'        => time(),
        ] + $options['params'];
    }

    /**
     * Generates the Request's signature based on the defined parameters.
     *
     * @param   Request $request    The Request to sign.
     * @param   array   $options    A reference to the options passed along with the Request.
     */
    protected function sign(Request $request, array& $options)
    {
        $signer = $options['signer'] ?? $this->signer;

        $options['params']['oauth_signature'] = $signer->sign(
            $request,
            $options['params'],
            $options['client'] ?? $this->client,
            $options['token']  ?? null
        );
    }

    /**
     * Handles the Request.
     *
     * @param   Request     $request        The request.
     * @param   array       $options        The options passed along with the Request.
     * @return  Request
     * @throws  \InvalidArgumentException   When an invalid authorization method is set.
     */
    protected function handle(Request $request, array $options) : Request
    {
        switch ($this->method) {
            case self::METHOD_HEADER:
                return $this->withHeader($request, $options['params']);
                break;

            case self::METHOD_QUERY:
                return $this->withQuery($request, $options['params']);
                break;
        }

        // Should never happen since the 'method' property is private and we're doing the checking in setMethod()
        // But...
        throw new \InvalidArgumentException("Authorization method [$this->method] is not supported by this middleware.");
    }

    /**
     * Creates a new Request with the Authorization protocol header applied to it.
     *
     * @param   Request $request    The base Request to add the header to.
     * @param   array   $params     The protocol parameters.
     * @return  Request
     */
    protected function withHeader(Request $request, array $params) : Request
    {
        // Percent encode the Authorization header parameters according to spec.
        foreach ($params as $key => $value) {
            $params[$key] = $key.'="'.rawurlencode($value).'"';
        }

        return $request->withHeader('Authorization', 'OAuth ' . implode(', ', $params));
    }

    /**
     * Creates a new Request with the protocol parameters appended to its query string.
     *
     * @param   Request $request    The base Request to add the query string to.
     * @param   array   $params     The protocol parameters.
     * @return  Request
     */
    protected function withQuery(Request $request, array $params) : Request
    {
        $uri = $request->getUri();

        parse_str($uri->getQuery(), $current);

        return $request->withUri($uri->withQuery(http_build_query($params + $current, '', '&', PHP_QUERY_RFC3986)));
    }
}
