<?php namespace nyx\auth\id\identities;

// Internal dependencies
use nyx\auth\id\protocols\oauth1;
use nyx\auth;

/**
 * Twitter Identity
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Twitter extends oauth1\Identity
{
    /**
     * {@inheritDoc}
     */
    protected static $provider = oauth1\providers\Twitter::class;

    /**
     * @var string  The description bound to the Identity.
     */
    protected $description;

    /**
     * @var string  The location bound to the Identity.
     */
    protected $location;

    /**
     * @var int     The follower count bound to the Identity.
     */
    protected $followersCount;

    /**
     * {@inheritDoc}
     */
    public function __construct(auth\interfaces\Credentials $credentials, array $data)
    {
        parent::__construct($credentials, $data);

        $this->username    = $data['screen_name']             ?? null;
        $this->name        = $data['name']                    ?? null;
        $this->avatar      = $data['profile_image_url_https'] ?? null;

        // @todo @decide Check $data['verified'] and discard unverified email addresses?
        $this->email       = $data['email']                   ?? null;

        // Additional data specific to Twitter.
        $this->location       = $data['location']              ?? null;
        $this->description    = $data['description']           ?? null;
        $this->followersCount = (int) $data['followers_count'] ?? null;
    }

    /**
     * Returns the description bound to the Identity.
     *
     * @return  string
     */
    public function getDescription() : ?string
    {
        return $this->description;
    }

    /**
     * Returns the location bound to the Identity.
     *
     * @return  string
     */
    public function getLocation() : ?string
    {
        return $this->location;
    }

    /**
     * Returns the follower count bound to the Identity.
     *
     * @return  int
     */
    public function getFollowersCount() : ?int
    {
        return $this->followersCount;
    }
}
