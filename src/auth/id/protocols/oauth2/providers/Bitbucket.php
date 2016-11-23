<?php namespace nyx\auth\id\protocols\oauth2\providers;

// External dependencies
use GuzzleHttp\Promise\PromiseInterface as Promise;

// Internal dependencies
use nyx\auth\id\protocols\oauth2;
use nyx\auth;

/**
 * Bitbucket Provider (OAuth 2.0)
 *
 * Bitbucket provides both OAuth version implementations and 2 versions of their public API. We are utilizing v2.0
 * of both of those.
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Bitbucket extends oauth2\Provider
{
    /**
     * {@inheritDoc}
     */
    const URL_AUTHORIZE = 'https://bitbucket.org/site/oauth2/authorize';
    const URL_EXCHANGE  = 'https://bitbucket.org/site/oauth2/access_token';
    const URL_IDENTIFY  = 'https://api.bitbucket.org/2.0/user';

    /**
     * {@inheritDoc}
     *
     * Note: self::createIdentity() is an override in Bitbucket's case - we provide subclasses to make a distinction
     * between User and Team Identities, but the below class name points to the shared parent of those.
     */
    const IDENTITY = auth\id\identities\Bitbucket::class;

    /**
     * {@inheritDoc}
     *
     * Note: BitBucket's OAuth 2.0 implementation does not currently (Sept 3rd 2016) support scope requests
     * on the Authorize requests. Scopes are defined on a per-consumer basis in their admin panel instead,
     * so this below is just a reminder of what scope needs to be set in the panel for this Provider to be able
     * to request basic Identity data. Also - the 'account' scope already includes the 'email' scope.
     */
    protected $defaultScopes = ['account'];

    /**
     * {@inheritdoc}
     */
    public function identify(oauth2\Token $token) : Promise
    {
        $promise = $this->request('GET', $this->getIdentifyUrl(), $token);

        // Bitbucket, similar to GitHub, makes an entity's e-mail addresses available at a different endpoint,
        // so if we are asked to fetch it, let's run that request in parallel to save some time on HTTP roundtrips.
        if ($this->shouldProvideEmailAddress()) {

            // Intercept the flow - instead of directly returning a Promise for the entity's identity data,
            // we will now return a Promise that resolves once both the email and identity
            // data have been resolved and the email has been mapped into the identity data.
            $promise = $this->getEmail($token)->then(function ($email) use ($token, $promise) {

                // Map the email in once the identity data is available (has succesfully resolved).
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
        return $this->request('GET', 'https://api.bitbucket.org/2.0/user/emails', $token)->then(function(array $data) {
            foreach ($data['values'] as $email) {
                if ($email['is_primary'] && $email['is_confirmed']) {
                    return $email['email'];
                }
            }
        });
    }

    /**
     * {@inheritdoc}
     *
     * Overridden because we return different Identity objects depending on what kind of entity's data we got.
     */
    protected function createIdentity(oauth2\Token $token, array $data) : oauth2\Identity
    {
        $class = $data['type'] === 'team'
            ? auth\id\identities\bitbucket\Team::class
            : auth\id\identities\bitbucket\User::class;

        return new $class($token, $data);
    }
}
