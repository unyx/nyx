<?php namespace nyx\auth\id\identities;

// Internal dependencies
use nyx\auth\id\protocols\oauth2;

/**
 * Bitbucket Identity
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Bitbucket extends oauth2\Identity
{
    /**
     * {@inheritDoc}
     */
    protected static $provider = oauth2\providers\Bitbucket::class;

    /**
     * @var string  The location bound to the Identity.
     */
    protected $location;

    /**
     * @var string  The website URL bound to the Identity.
     */
    protected $website;

    /**
     * @var string  The time the Identity was created at.
     */
    protected $createdAt;

    /**
     * {@inheritDoc}
     */
    public function __construct(oauth2\Token $token, array $data)
    {
        // Note: Bitbucket's UUIDs are wrapped by curly brackets - we are not
        // transforming that in any way.
        $this->id         = $data['uuid']         ?? null;
        $this->username   = $data['username']     ?? null;
        $this->name       = $data['display_name'] ?? null;
        $this->email      = $data['email']        ?? null;
        $this->location   = $data['location']     ?? null;
        $this->website    = $data['website']      ?? null;
        $this->createdAt  = $data['created_on']   ?? null;

        parent::__construct($token, $data);
    }
}
