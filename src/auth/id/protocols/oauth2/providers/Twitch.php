<?php namespace nyx\auth\id\protocols\oauth2\providers;

// Internal dependencies
use nyx\auth\id\protocols\oauth2;
use nyx\auth;

/**
 * Twitch Identity Provider (OAuth 2.0)
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Twitch extends oauth2\Provider
{
    /**
     * {@inheritDoc}
     */
    const URL_AUTHORIZE = 'https://api.twitch.tv/kraken/oauth2/authorize';
    const URL_EXCHANGE  = 'https://api.twitch.tv/kraken/oauth2/token';
    const URL_IDENTIFY  = 'https://api.twitch.tv/kraken/user';

    /**
     * {@inheritDoc}
     */
    const IDENTITY = auth\id\identities\Twitch::class;

    /**
     * {@inheritDoc}
     */
    protected $defaultScopes = ['user_read'];

    /**
     * {@inheritDoc}
     */
    protected function getDefaultRequestOptions(auth\interfaces\Token $token = null) : array
    {
        $options = [
            'headers' => [
                'Accept' => 'application/vnd.twitchtv.v3+json'
            ]
        ];

        // Twitch uses a non-default authorization header.
        if (null !== $token) {
            $options['headers']['Authorization'] = 'OAuth '.$token->getId();
        }

        // Note: No parent call in the flow.
        return $options;
    }
}
