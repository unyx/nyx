<?php namespace nyx\auth\id\protocols\oauth2\providers;

// Internal dependencies
use nyx\auth\id\protocols\oauth2;
use nyx\auth;

/**
 * Google Identity Provider (OAuth 2.0)
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Google extends oauth2\Provider
{
    /**
     * {@inheritDoC}
     */
    const SCOPE_SEPARATOR = ' ';

    /**
     * {@inheritDoc}
     */
    const URL_AUTHORIZE = 'https://accounts.google.com/o/oauth2/auth';
    const URL_EXCHANGE  = 'https://accounts.google.com/o/oauth2/token';
    const URL_IDENTIFY  = 'https://www.googleapis.com/plus/v1/people/me';

    /**
     * {@inheritDoc}
     */
    const IDENTITY = auth\id\identities\Google::class;

    /**
     * {@inheritDoc}
     */
    protected $defaultScopes = ['openid', 'profile', 'email'];
}
