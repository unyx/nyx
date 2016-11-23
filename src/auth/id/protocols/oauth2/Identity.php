<?php namespace nyx\auth\id\protocols\oauth2;

// Internal dependencies
use nyx\auth\id;

/**
 * OAuth 2.0 Identity
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
abstract class Identity extends id\Identity
{
    /**
     * {@inheritDoc}
     *
     * Overridden for stricter type-hint on $token.
     */
    public function __construct(Token $token, array $data)
    {
        parent::__construct($token, $data);
    }
}
