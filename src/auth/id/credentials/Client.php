<?php namespace nyx\auth\id\credentials;

// Internal dependencies
use nyx\auth;

/**
 * Client Credentials
 *
 * A special set of Credentials useful in, for example, an oAuth context where the client application's callback URI
 * can be treated equally important in the authentication process as its ID and secret.
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Client extends auth\Credentials
{
    /**
     * @var string  The redirect (callback) URI associated with the application.
     */
    protected $redirectUri;

    /**
     * {@inheritDoc}
     *
     * @param   string  $redirectUri    The redirect (callback) URI associated with the application.
     */
    public function __construct(string $id, $secret, string $redirectUri)
    {
        parent::__construct($id, $secret);

        $this->redirectUri = $redirectUri;
    }

    /**
     * Returns the redirect (callback) URI associated with the application.
     *
     * @return  string
     */
    public function getRedirectUri() : string
    {
        return $this->redirectUri;
    }

    /**
     * {@inheritDoc}
     */
    public function matches(auth\interfaces\Token $that) : bool
    {
        if (!$that instanceof static) {
            return false;
        }

        if ($this->redirectUri !== $that->getRedirectUri()) {
            return false;
        }

        return parent::matches($that);
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($data)
    {
        $data = unserialize($data);

        $this->id          = $data['id'];
        $this->secret      = $data['secret'];
        $this->redirectUri = $data['redirectUri'];
    }

    /**
     * {@inheritDoc}
     */
    public function toArray() : array
    {
        return [
            'id'          => $this->id,
            'secret'      => $this->secret,
            'redirectUri' => $this->redirectUri
        ];
    }
}
