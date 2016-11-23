<?php namespace nyx\auth\id\protocols\oauth1\providers;

// Internal dependencies
use nyx\auth\id\protocols\oauth1;
use nyx\auth;

/**
 * Bitbucket Provider (OAuth 1.0a)
 *
 * This is a valid consumer of BitBucket's OAuth 1.0 identity provider implementation, falling back on their v1.0 API
 * to retrieve Identity data. However, for consistency we decided to use Bitbucket's OAuth 2.0 variant as the default
 * for moving forward.
 *
 * If you want to utilize this Provider, override its createIdentity() method or set its IDENTITY class constant
 * to point to a child class of oauth1\Identity of your choosing.
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 * ----
 * @see         \nyx\auth\id\protocols\oauth2\providers\Bitbucket
 */
abstract class Bitbucket extends oauth1\Provider
{
    /**
     * {@inheritDoc}
     */
    const URL_REQUEST   = 'https://bitbucket.org/api/1.0/oauth/request_token';
    const URL_AUTHORIZE = 'https://bitbucket.org/api/1.0/oauth/authenticate';
    const URL_EXCHANGE  = 'https://bitbucket.org/api/1.0/oauth/access_token';
    const URL_IDENTIFY  = 'https://bitbucket.org/api/1.0/user';
}
