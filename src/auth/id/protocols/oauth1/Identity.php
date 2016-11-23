<?php namespace nyx\auth\id\protocols\oauth1;

// Internal dependencies
use nyx\auth;

/**
 * OAuth 1.0a Identity
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
abstract class Identity extends auth\id\Identity
{
    /**
     * {@inheritDoc}
     *
     * Overridden for stricter type-hint on $credentials.
     */
    public function __construct(auth\interfaces\Credentials $credentials, array $data)
    {
        parent::__construct($credentials, $data);
    }
}
