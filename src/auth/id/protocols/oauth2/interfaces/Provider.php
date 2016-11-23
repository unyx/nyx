<?php namespace nyx\auth\id\protocols\oauth2\interfaces;

// Internal dependencies
use nyx\auth\id\protocols\oauth2;
use nyx\auth\id;

/**
 * OAuth 2.0 Provider Interface
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Provider extends id\interfaces\Provider
{
    /**
     * Performs an identify Request to the Provider, returning information about the entity (the Identity) whose
     * OAuth 2.0 Access Token is used to perform the Request.
     *
     * @param   oauth2\Token        $token          A valid OAuth 2.0 Access Token.
     * @return  oauth2\Identity                     The Identity of the Credentials' owning entity.
     */
    public function identify(oauth2\Token $token) : oauth2\Identity;
}
