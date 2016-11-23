<?php namespace nyx\auth\id\protocols\oauth2;

// External dependencies
use GuzzleHttp\Promise\PromiseInterface as Promise;
use nyx\utils;

// Internal dependencies
use nyx\auth\id\protocols\oauth2;
use nyx\auth;

/**
 * OAuth 2.0 Provider
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
abstract class Provider extends auth\id\Provider implements interfaces\Provider
{
    /**
     * The character separating different scopes in the request.
     */
    const SCOPE_SEPARATOR = ',';

    /**
     * @var array   The default access scopes to be requested during the authorization step.
     */
    protected $defaultScopes = [];

    /**
     * {@inheritDoc}
     */
    public function authorize(callable $redirect, array $parameters = [])
    {
        $parameters += [
            'scope'         => implode(static::SCOPE_SEPARATOR, $this->defaultScopes),
            'response_type' => 'code'
        ];

        // The explicitly set Client Credentials will always overwrite the keys' values from the optional
        // $parameters if they are present. If you want to use other credentials, either use the getAuthorizeUrl()
        // method directly or instantiate a Provider with different consumer credentials.
        $parameters['client_id']    = $this->consumer->getId();
        $parameters['redirect_uri'] = $this->consumer->getRedirectUri();

        // Only doing an isset here - can't easily predict valid values nor force properly randomized values.
        // Invalid requests will be rejected by the endpoint, after all.
        $parameters['state'] = $parameters['state'] ?? utils\Random::string(16);

        // The state gets passed along explicitly as the second argument since it will *always* need to be persisted
        // in some way by the end-user until it can be discarded after a successful exchange.
        return $redirect($this->getAuthorizeUrl($parameters), $parameters['state'], $parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function exchange(string $code) : Promise
    {
        return $this->request('POST', $this->getExchangeUrl(), null, [
            'form_params' => [
                'client_id'     => $this->consumer->getId(),
                'client_secret' => $this->consumer->getSecret(),
                'redirect_uri'  => $this->consumer->getRedirectUri(),
                'grant_type'    => 'authorization_code',
                'code'          => $code
            ]
        ])->then(function (array $data) {
            return $this->createToken($data);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function identify(oauth2\Token $token) : Promise
    {
        return $this->request('GET', $this->getIdentifyUrl(), $token)->then(function (array $data) use ($token) {
            return $this->createIdentity($token, $data);
        });
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultRequestOptions(auth\interfaces\Token $token = null) : array
    {
        $options = parent::getDefaultRequestOptions($token);

        // If the $token is explicitly given, it will override the respective authorization header.
        if (isset($token)) {
            $options['headers']['Authorization'] = 'Bearer '.$token;
        }

        return $options;
    }

    /**
     * Creates an OAuth 2.0 Access Token instance based on raw response data.
     *
     * @param   array           $data   The raw (response) data to base on.
     * @return  oauth2\Token            The resulting OAuth 2.0 Access Token instance.
     * @throws  \RuntimeException       When the data did not contain an access token in a recognized format.
     */
    protected function createToken(array $data) : oauth2\Token
    {
        // The HTTP Client will throw on an unsuccessful response, but we'll double check that we actually got
        // an access token in response.
        if (empty($data['access_token'])) {
            throw new \RuntimeException('The Provider did not return an access token or it was in an unrecognized format.');
        }

        $token = new oauth2\Token($data['access_token']);

        if (!empty($data['refresh_token'])) {
            $token->setRefreshToken($data['refresh_token']);
        }

        if (!empty($data['expires_in'])) {
            $token->setExpiry($data['expires_in']);
        }

        // Some providers, like Github or Slack, return the granted scopes along with the Tokens. Let's make
        // use of that in the base class since an isset isn't exactly expensive and if other providers happen
        // to return the scopes under a different key, child classes can just remap the value.
        if (isset($data['scope'])) {
            $token->setScopes(is_array($data['scope']) ? $data['scope'] : explode(static::SCOPE_SEPARATOR, $data['scope']));
        }

        return $token;
    }
}
