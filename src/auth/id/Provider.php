<?php namespace nyx\auth\id;

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
     * identify calls.
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
     * identify calls.
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
     * Builds an URL string from the given base URL and optional additional query parameters.
     *
     * @param   string              $base       The base URL.
     * @param   array               $query      Additional query parameters.
     * @return  string
     */
    protected function buildUrl(string $base, array $query = []) : string
    {
        return empty($query) ? $base : $base.'?'.http_build_query($query, null, '&');
    }

    /**
     * Returns the underlying HTTP Client used for communicating with the provider.
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
}
