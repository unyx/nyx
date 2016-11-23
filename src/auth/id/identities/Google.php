<?php namespace nyx\auth\id\identities;

// Internal dependencies
use nyx\auth\id\protocols\oauth2;

/**
 * Google Identity
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Google extends oauth2\Identity
{
    /**
     * {@inheritDoc}
     */
    protected static $provider = oauth2\providers\Google::class;

    /**
     * {@inheritDoc}
     */
    public function __construct(oauth2\Token $token, array $data)
    {
        parent::__construct($token, $data);

        $this->username = $data['nickname']           ?? null;
        $this->name     = $data['displayName']        ?? null;
        $this->email    = $data['emails'][0]['value'] ?? null;

        if (isset($data['image'])) {
            $this->avatar = $data['image']['url']     ?? null;
        }
    }
}
