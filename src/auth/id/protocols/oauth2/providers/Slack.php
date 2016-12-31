<?php namespace nyx\auth\id\protocols\oauth2\providers;

// Internal dependencies
use nyx\auth\id\protocols\oauth2;
use nyx\auth;

/**
 * Slack Identity Provider (OAuth 2.0)
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Slack extends oauth2\Provider
{
    /**
     * {@inheritDoc}
     */
    const URL_AUTHORIZE = 'https://slack.com/oauth/authorize';
    const URL_EXCHANGE  = 'https://slack.com/api/oauth.access';
    const URL_IDENTIFY  = 'https://slack.com/api/users.identity';

    /**
     * {@inheritDoc}
     */
    const IDENTITY = auth\id\identities\Slack::class;

    /**
     * {@inheritDoc}
     *
     * Note: The "identity" scopes cannot be requested alongside other scopes, as Slack otherwise
     * returns an 'invalid_scope' error.
     */
    protected $defaultScopes = ['identity.basic', 'identity.email', 'identity.avatar'];

    /**
     * {@inheritDoc}
     */
    protected function getDefaultRequestOptions(auth\interfaces\Token $token = null) : array
    {
        if (null === $token) {
            return parent::getDefaultRequestOptions($token);
        }

        // Slack expects the token to be present in each authorized request as the 'token' parameter.
        // Note: Passing it in the body yielded 'not_authed' (ie. no token) errors from Slack.
        return array_merge_recursive(parent::getDefaultRequestOptions(), [
            'query' => [
                'token' => $token->getId()
            ]
        ]);
    }
}
