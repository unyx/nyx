<?php namespace nyx\auth\id\protocols\oauth2;

// External dependencies
use nyx\core;

// Internal dependencies
use nyx\auth\id\protocols\oauth2;
use nyx\auth;

/**
 * OAuth 2.0 Access Token
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Token extends auth\Token
{
    /**
     * The traits of a OAuth 2.0 Access Token instance.
     */
    use core\traits\Serializable;

    /**
     * @var auth\Token  The refresh Token for this Access Token.
     */
    protected $refreshToken;

    /**
     * @var int     The expiry time of this Token in seconds.
     */
    protected $expiry;

    /**
     * @var array   The scopes this Token is known to grant access to, if applicable.
     *              Note: For faster lookups the actual scopes are stored as keys of this array.
     */
    protected $scopes = [];

    /**
     * {@inheritDoc}
     */
    public function matches(auth\interfaces\Token $that) : bool
    {
        // The interface alone won't do us much good here. We do need access to specific properties.
        if (!$that instanceof static) {
            return false;
        }

        // Base class(es) will perform common comparisons.
        if (!parent::matches($that)) {
            return false;
        }

        if ($this->expiry !== $that->expiry) {
            return false;
        }

        if (isset($this->refreshToken) && !$this->refreshToken->matches($that->refreshToken)) {
            return false;
        }

        return $this->scopes === $that->scopes;
    }

    /**
     * Determines whether this Token is known to grant access to a specific scope.
     *
     * @param   string  $scope  The name of the scope to check for.
     * @return  bool
     */
    public function authorizes(string $scope) : bool
    {
        return isset($this->scopes[$scope]);
    }

    /**
     * Returns the scopes this Token is known to grant access to.
     *
     * @return  array
     */
    public function getScopes() : array
    {
        return $this->scopes;
    }

    /**
     * Sets the scopes this Token is known to grant access to.
     *
     * @param   array   $scopes The scopes to set.
     * @return  $this
     */
    public function setScopes(array $scopes) : oauth2\Token
    {
        $this->scopes = [];

        foreach ($scopes as $scope) {
            $this->scopes[$scope] = true;
        }

        return $this;
    }

    /**
     * Returns the refresh Token for this Access Token.
     *
     * @return  auth\Token
     */
    public function getRefreshToken() : ?auth\Token
    {
        return $this->refreshToken;
    }

    /**
     * Sets the refresh Token for this Access Token.
     *
     * @param   auth\Token|string   $token
     * @return  $this
     */
    public function setRefreshToken($token) : oauth2\Token
    {
        // For any type other than an auth\Token instance, instantiate a auth\Token.
        // However, only strings are actually accepted in this scenario (the constructor will throw
        // on invalid types).
        if (!$token instanceof auth\Token) {
            $token = new auth\Token($token);
        }

        $this->refreshToken = $token;

        return $this;
    }

    /**
     * Returns the expiry time of this Token in seconds.
     *
     * @return  int
     */
    public function getExpiry() : ?int
    {
        return $this->expiry;
    }

    /**
     * Sets the expiry time of this Token in seconds.
     *
     * @param   int $time   The expiry time in seconds.
     * @return  $this
     */
    public function setExpiry(int $time) : oauth2\Token
    {
        $this->expiry = $time;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($data)
    {
        $data = unserialize($data);

        $this->id           = $data['id'];
        $this->refreshToken = $data['refresh'];
        $this->expiry       = $data['expiry'];

        $this->setScopes($data['scopes']);
    }

    /**
     * {@inheritDoc}
     */
    public function toString() : string
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray() : array
    {
        return [
            'id'      => $this->id,
            'refresh' => $this->refreshToken,
            'expiry'  => $this->expiry,
            'scopes'  => array_keys($this->scopes)
        ];
    }
}
