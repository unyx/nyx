<?php namespace nyx\auth\id\identities;

// Internal dependencies
use nyx\auth\id\protocols\oauth2;

/**
 * Twitch Identity
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Twitch extends oauth2\Identity
{
    /**
     * {@inheritDoc}
     */
    protected static $provider = oauth2\providers\Twitch::class;

    /**
     * {@inheritDoc}
     */
    public function __construct(oauth2\Token $token, array $data)
    {
        $this->id          = $data['_id']          ?? null;
        $this->username    = $data['display_name'] ?? null;
        $this->email       = $data['email']        ?? null;
        $this->avatar      = $data['logo']         ?? null;

        $this->description = $data['bio']          ?? null;

        parent::__construct($token, $data);
    }
}
