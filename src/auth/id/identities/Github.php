<?php namespace nyx\auth\id\identities;

// Internal dependencies
use nyx\auth\id\protocols\oauth2;

/**
 * GitHub Identity
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Github extends oauth2\Identity
{
    /**
     * {@inheritDoc}
     */
    protected static $provider = oauth2\providers\Github::class;

    /**
     * {@inheritDoc}
     */
    public function __construct(oauth2\Token $token, array $data)
    {
        parent::__construct($token, $data);

        $this->username = $data['login']      ?? null;
        $this->name     = $data['name']       ?? null;
        $this->email    = $data['email']      ?? null;
        $this->avatar   = $data['avatar_url'] ?? null;
    }
}
