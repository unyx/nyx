<?php namespace nyx\auth\id\protocols\oauth2\providers;

// Internal dependencies
use nyx\auth\id\protocols\oauth2;
use nyx\auth;

/**
 * LinkedIn Identity Provider (OAuth 2.0)
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class LinkedIn extends oauth2\Provider
{
    /**
     * {@inheritDoc}
     */
    const SCOPE_SEPARATOR = ' ';

    /**
     * {@inheritDoc}
     */
    const URL_AUTHORIZE = 'https://www.linkedin.com/oauth/v2/authorization';
    const URL_EXCHANGE  = 'https://www.linkedin.com/oauth/v2/accessToken';
    const URL_IDENTIFY  = 'https://api.linkedin.com/v1/people';

    /**
     * {@inheritDoc}
     */
    const IDENTITY = auth\id\identities\LinkedIn::class;

    /**
     * {@inheritDoc}
     */
    protected $defaultScopes = ['r_basicprofile', 'r_emailaddress'];

    /**
     * {@inheritDoc}
     */
    public function getIdentifyUrl() : string
    {
        $fields = [
            'id',                   'first-name',   'last-name',    'formatted-name',
            'email-address',        'headline',     'location',     'industry',
            'public-profile-url',   'picture-url',
        ];

        // Talk about proprietary 'standards'...
        return static::URL_IDENTIFY.'/~:('.implode(',', $fields).')';
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultRequestOptions(auth\interfaces\Token $token = null) : array
    {
        return array_merge_recursive(parent::getDefaultRequestOptions($token), [
            'headers' => [
                'x-li-format' => 'json'
            ]
        ]);
    }
}
