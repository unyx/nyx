<?php namespace nyx\auth\id;

// External dependencies
use Psr\Http\Message\ResponseInterface as Response;
use GuzzleHttp\Promise\PromiseInterface as Promise;

// Internal dependencies
use nyx\auth;

/**
 * Identity Provider
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
abstract class Provider implements interfaces\Provider
{
    /**
     * @var credentials\Client  The credentials used to identify this consumer with the provider.
     */
    protected $consumer;

    /**
     * @var bool    Whether the Provider should attempt to retrieve the email address of an entity when performing
     *              identify calls.
     *
     *              This is kept as a separate, publicly settable behavioural flag because many Providers make
     *              the entity's email address(es) available only with special permission scopes and/or at different
     *              endpoints, meaning simple identify() calls which do not rely on the email being available
     *              can in those cases be simplified by setting this flag to false.
     *
     *              Some Identity Providers do not provide the entity's email address(es) under any circumstances,
     *              in which case this flag will have no effect.
     */
    protected $shouldProvideEmailAddress = true;

    /**
     * @var \GuzzleHttp\ClientInterface The underlying HTTP Client used for communicating with the provider.
     */
    protected $httpClient;

    /**
     * Constructs a new Identity Provider instance tied to the given client/consumer/application Credentials.
     *
     * @param   credentials\Client  $consumer
     */
    public function __construct(credentials\Client $consumer)
    {
        $this->consumer = $consumer;
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthorizeUrl(array $parameters = []) : string
    {
        return $this->buildUrl(static::URL_AUTHORIZE, $parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function getExchangeUrl() : string
    {
        return static::URL_EXCHANGE;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifyUrl() : string
    {
        return static::URL_IDENTIFY;
    }

    /**
     * Checks whether the Provider should attempt to retrieve the email address of an entity when performing
     * identify requests.
     *
     * @see     $shouldProvideEmailAddress
     * @return  bool
     */
    public function shouldProvideEmailAddress() : bool
    {
        return $this->shouldProvideEmailAddress;
    }

    /**
     * Sets whether the Provider should attempt to retrieve the email address of an entity when performing
     * identify requests.
     *
     * @param   bool    $bool
     * @return  $this
     */
    public function setShouldProvideEmailAddress(bool $bool) : Provider
    {
        $this->shouldProvideEmailAddress = $bool;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function request(string $method, string $url, auth\interfaces\Token $token = null, array $options = []) : Promise
    {
        return $this->getHttpClient()->requestAsync($method, $url, array_merge_recursive($this->getDefaultRequestOptions($token), $options))->then(
            function (Response $response) use($token) {
                return $this->onRequestSuccess($response, $token);
            },
            function(\Exception $exception) use($token) {
                return $this->onRequestError($exception, $token);
            });
    }

    /**
     * Success callback for self::request().
     *
     * Note: Some Providers may return valid HTTP response codes for requests that were actually unsuccessful
     * and instead provide arbitrary responses like "ok => false" or "errors => []". Those misbehaving cases should
     * be caught within specific Providers.
     *
     * @param   Response                $response   The Response received to the Request made.
     * @param   auth\interfaces\Token   $token      The Token that was used to authorize the Request, if applicable.
     * @return  mixed
     */
    protected function onRequestSuccess(Response $response, auth\interfaces\Token $token = null)
    {
        return json_decode($response->getBody(), true);
    }

    /**
     * Failure callback for self::request().
     *
     * @param   \Exception              $exception  The Exception that occurred during the Request.
     * @param   auth\interfaces\Token   $token      The Token that was used to authorize the Request, if applicable.
     * @throws  \Exception                          Always re-throws the Exception. Child classes may, however, provide
     *                                              recovery paths.
     * @return  mixed
     */
    protected function onRequestError(\Exception $exception, auth\interfaces\Token $token = null)
    {
        throw $exception;
    }

    /**
     * Returns the default options (in a format recognized by Guzzle) for requests made by this Provider.
     *
     * @param   auth\interfaces\Token   $token      The Token that should be used to authorize the Request.
     * @return  array
     */
    protected function getDefaultRequestOptions(auth\interfaces\Token $token = null) : array
    {
        return [
            'headers' => [
                'Accept' => 'application/json'
            ]
        ];
    }

    /**
     * Builds an URL string from the given base URL and optional additional query parameters.
     *
     * @param   string  $base   The base URL.
     * @param   array   $query  Additional query parameters.
     * @return  string
     */
    protected function buildUrl(string $base, array $query = []) : string
    {
        return empty($query) ? $base : $base.'?'.http_build_query($query, null, '&');
    }

    /**
     * Returns the underlying HTTP Client used for communicating with the provider. Lazily instantiates
     * a HTTP Client if none is set yet.
     *
     * @return  \GuzzleHttp\ClientInterface
     */
    protected function getHttpClient() : \GuzzleHttp\ClientInterface
    {
        return $this->httpClient ?: $this->httpClient = new \GuzzleHttp\Client();
    }

    /**
     * Sets the underlying HTTP Client used for communicating with the provider.
     *
     * @param   \GuzzleHttp\ClientInterface $client
     * @return  $this
     */
    public function setHttpClient(\GuzzleHttp\ClientInterface $client) : Provider
    {
        $this->httpClient = $client;

        return $this;
    }

    /**
     * Creates an Identity instance of a type specific to the Provider, using a Token and raw data also
     * specific to the Provider.
     *
     * @param   auth\interfaces\Token   $token  The Token that had been used to retrieve the data about the entity.
     * @param   array                   $data   The raw data about the entity given by the Provider.
     * @return  interfaces\Identity             The resulting Identity instance.
     */
    protected function createIdentity(auth\interfaces\Token $token, array $data) : interfaces\Identity
    {
        $class = static::IDENTITY;

        return new $class($token, $data);
    }
}
