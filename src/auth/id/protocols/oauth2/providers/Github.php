<?php namespace nyx\auth\id\protocols\oauth2\providers;

// External dependencies
use Psr\Http\Message\ResponseInterface as Response;
use GuzzleHttp\Promise\PromiseInterface as Promise;

// Internal dependencies
use nyx\auth\id\protocols\oauth2;
use nyx\auth;

/**
 * GitHub Provider (OAuth 2.0)
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Github extends oauth2\Provider
{
    /**
     * {@inheritDoc}
     */
    const URL_AUTHORIZE = 'https://github.com/login/oauth/authorize';
    const URL_EXCHANGE  = 'https://github.com/login/oauth/access_token';
    const URL_IDENTIFY  = 'https://api.github.com/user';

    /**
     * {@inheritDoc}
     */
    const IDENTITY = auth\id\identities\Github::class;

    /**
     * {@inheritDoc}
     */
    protected $defaultScopes = ['user:email'];

    /**
     * {@inheritdoc}
     */
    public function identify(oauth2\Token $token) : Promise
    {
        $promise = $this->request('GET', $this->getIdentifyUrl(), $token);

        // GitHub makes an entity's e-mail addresses available at a different endpoint, so if we are asked
        // to fetch it, let's run that request in parallel to save some time on HTTP roundtrips.
        if ($this->shouldProvideEmailAddress()) {

            // Intercept the flow - instead of directly returning a Promise for the entity's identity data,
            // we will now return a Promise that resolves once both the email and identity
            // data have been resolved and the email has been mapped into the identity data.
            $promise = $this->getEmail($token)->then(function ($email) use ($token, $promise) {

                // Map the e-mail address in once the identity data is available (has successfully resolved).
                return $promise->then(function (array $data) use ($token, $email) {

                    $data['email'] = $email ?? $data['email'];

                    return $data;
                });
            });
        }

        return $promise->then(function (array $data) use ($token) {
            return $this->createIdentity($token, $data);
        });
    }

    /**
     * Returns a Promise for the e-mail address (primary and verified) belonging to the entity whose Access Token
     * gets used to request that data.
     *
     * @param   oauth2\Token    $token  The Access Token to use.
     * @return  Promise                 A Promise for the entity's e-mail address.
     */
    protected function getEmail(oauth2\Token $token) : Promise
    {
        return $this->request('GET', 'https://api.github.com/user/emails', $token)->then(function(array $data) {
            foreach ($data as $email) {
                if ($email['primary'] && $email['verified']) {
                    return $email['email'];
                }
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    protected function onRequestSuccess(Response $response, auth\interfaces\Token $token = null)
    {
        // GitHub provides the currently granted scopes with each response, so let's make use of that
        // and update our Token to reflect the currently granted scopes (Note: Users on GitHub can partially
        // revoke scope authorizations so it's wise to keep track of that even if the tokens themselves do not
        // expire unless revoked manually).
        if ($token instanceof oauth2\Token) {
            $token->setScopes($response->getHeader('X-OAuth-Scopes'));
        }

        return parent::onRequestSuccess($response, $token);
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultRequestOptions(auth\interfaces\Token $token = null) : array
    {
        return array_merge_recursive(parent::getDefaultRequestOptions($token), [
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json'
            ]
        ]);
    }
}
