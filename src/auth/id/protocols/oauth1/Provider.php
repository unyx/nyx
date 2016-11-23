<?php namespace nyx\auth\id\protocols\oauth1;

// External dependencies
use Psr\Http\Message\ResponseInterface as Response;
use GuzzleHttp\Promise\PromiseInterface as Promise;

// Internal dependencies
use nyx\auth\id;
use nyx\auth;

/**
 * Base OAuth 1.0a Provider
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
abstract class Provider extends id\Provider implements interfaces\Provider
{
    /**
     * @var interfaces\Signer   The Signer used for generating OAuth 1.0a compliant signatures of Requests
     *                          sent to this Identity Provider service.
     */
    protected $signer;

    /**
     * {@inheritDoc}
     *
     * @param   interfaces\Signer   $signer     The Signer to use for generating OAuth 1.0a compliant signatures
     *                                          of Requests sent to this Identity Provider service.
     */
    public function __construct(id\credentials\Client $consumer, interfaces\Signer $signer = null)
    {
        parent::__construct($consumer);

        $this->signer = $signer;
    }

    /**
     * {@inheritDoc}
     */
    public function getTemporaryCredentialsUrl() : string
    {
        return static::URL_REQUEST;
    }

    /**
     * {@inheritDoc}
     */
    public function authorize(callable $redirect, array $parameters = [])
    {
        if (!isset($parameters['oauth_token'])) {
            $temporaryCredentials = $this->handshake()->wait();

            // We will pass along the token to the authorize redirect, but the secret for the
            // upcoming exchange needs to be persisted until the exchange happens, so we'll hand it back
            // to the provided callable to deal with that.
            $parameters['oauth_token'] = $temporaryCredentials->getId();
        } else {
            $temporaryCredentials = null;
        }

        return $redirect($this->getAuthorizeUrl($parameters), $temporaryCredentials, $parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function handshake() : Promise
    {
        return $this->requestCredentials($this->getTemporaryCredentialsUrl(), [
            'oauth1' => [
                'callback' => true
            ]
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function exchange(auth\Credentials $temporary, string $verifier) : Promise
    {
        return $this->requestCredentials($this->getExchangeUrl(), [
            'form_params' => [
                'oauth_verifier' => $verifier
            ]
        ], $temporary);
    }

    /**
     * {@inheritDoc}
     */
    public function identify(auth\Credentials $credentials) : Promise
    {
        return $this->request('GET', $this->getIdentifyUrl(), $credentials)->then(function (array $data) use ($credentials) {
            return $this->createIdentity($credentials, $data);
        });
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultRequestOptions(auth\interfaces\Token $token = null) : array
    {
        // We are doing the union even if $token is actually not set - we need the "oauth1" key in the options
        // to be not null so that our authorization middleware can pick it up and do its magic.
        return [
            'oauth1' => [
                'token' => $token
            ]
        ] + parent::getDefaultRequestOptions($token);
    }

    /**
     * {@inheritDoc}
     *
     * Overridden as we need to instantiate a custom middleware stack that includes our authorization
     * middleware for Guzzle.
     */
    protected function getHttpClient() : \GuzzleHttp\ClientInterface
    {
        if (!isset($this->httpClient)) {

            $stack = \GuzzleHttp\HandlerStack::create();
            $stack->push(new middlewares\Authorization($this->consumer, $this->getSigner()), 'authorization');

            $this->httpClient = new \GuzzleHttp\Client(['handler' => $stack]);
        }

        return $this->httpClient;
    }

    /**
     * Returns the Signer used for generating OAuth 1.0a compliant signatures of Requests sent to this
     * Identity Provider service. Lazily instantiates a default HMAC-SHA1 Signer if no Signer is set yet.
     *
     * @return  interfaces\Signer
     */
    protected function getSigner() : interfaces\Signer
    {
        return $this->signer ?: $this->signer = new signers\HmacSha1();
    }

    /**
     * Performs a POST request to the specified URL assuming the Response will contain form-encoded
     * OAuth 1.0a credentials, either temporary or proper, which will then be used to create a auth\Credentials
     * instance out of.
     *
     * @param   string              $url        The URL to query.
     * @param   array               $options    Additional request options (will be merged with the defaults).
     * @param   auth\Credentials    $temporary  A set of temporary credentials. Must be set when requesting
     *                                          proper credentials as it affects how the middleware signs the request.
     *                                          Must *not* be set when requesting those temporary credentials as this
     *                                          in turn affects how the response is verified.
     * @return  Promise                         A Promise for a set of Credentials (an auth\Credentials instance).
     */
    protected function requestCredentials(string $url, array $options, auth\Credentials $temporary = null) : Promise
    {
        return $this->getHttpClient()
            ->requestAsync('POST', $url, array_merge_recursive($this->getDefaultRequestOptions($temporary), $options))
            ->then(function (Response $response) use($temporary) {

                // We are assuming a form-encoded body here - for any Provider that does not return those in this format
                // for handshakes and exchanges this will require refactoring.
                parse_str((string) $response->getBody(), $data);

                if (!is_array($data) || !isset($data['oauth_token']) || !isset($data['oauth_token_secret'])) {
                    throw new \Exception('Failed to parse the Response after requesting OAuth 1.0a credentials.');
                }

                if (isset($data['error'])) {
                    throw new \Exception("Failed to retrieve credentials: {$data['error']}.");
                }

                // Only check this if we've got a Response to a request for temporary credentials (eg. the temporary
                // credentials were not given).
                if (!isset($temporary) && (!isset($data['oauth_callback_confirmed']) || $data['oauth_callback_confirmed'] != 'true')) {
                    throw new \Exception('Failed to retrieve temporary credentials.');
                }

                return new auth\Credentials($data['oauth_token'], $data['oauth_token_secret']);
            });
    }
}
