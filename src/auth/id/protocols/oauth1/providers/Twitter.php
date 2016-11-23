<?php namespace nyx\auth\id\protocols\oauth1\providers;

// Internal dependencies
use nyx\auth\id\protocols\oauth1;
use nyx\auth;

/**
 * Twitter Identity Provider (OAuth 1.0a)
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Twitter extends oauth1\Provider
{
    /**
     * {@inheritDoc}
     */
    const URL_REQUEST   = 'https://api.twitter.com/oauth/request_token';
    const URL_AUTHORIZE = 'https://api.twitter.com/oauth/authenticate';
    const URL_EXCHANGE  = 'https://api.twitter.com/oauth/access_token';
    const URL_IDENTIFY  = 'https://api.twitter.com/1.1/account/verify_credentials.json';

    /**
     * {@inheritDoc}
     */
    const IDENTITY = auth\id\identities\Twitter::class;

    /**
     * {@inheritDoc}
     */
    public function getIdentifyUrl() : string
    {
        return static::URL_IDENTIFY . $this->shouldProvideEmailAddress() ? '?include_email=true' : '';
    }
}
