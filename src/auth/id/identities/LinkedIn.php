<?php namespace nyx\auth\id\identities;

// Internal dependencies
use nyx\auth\id\protocols\oauth2;

/**
 * LinkedIn Identity
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class LinkedIn extends oauth2\Identity
{
    /**
     * {@inheritDoc}
     */
    protected static $provider = oauth2\providers\LinkedIn::class;

    /**
     * {@inheritDoc}
     */
    public function __construct(oauth2\Token $token, array $data)
    {
        $this->name    = $data['formattedName'] ?? null;
        $this->email   = $data['emailAddress']  ?? null;
        $this->avatar  = $data['pictureUrl']    ?? null;

        parent::__construct($token, $data);
    }
}
