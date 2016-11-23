<?php namespace nyx\auth\id;

// Internal dependencies
use nyx\auth;

/**
 * Base Identity
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 * @todo        Private access to some properties (ID at the very least? Type is currently not enforced on assignment).
 */
abstract class Identity implements interfaces\Identity
{
    /**
     * @var string  The fully qualified class name of this Identity's Provider.
     */
    protected static $provider;

    /**
     * @var string  The unique identifier of the Identity specific to its Provider.
     */
    protected $id;

    /**
     * @var string  The username/nickname/display name bound to the Identity.
     */
    protected $username;

    /**
     * @var string  The full name bound to the Identity.
     */
    protected $name;

    /**
     * @var string  The e-mail address bound to the Identity.
     */
    protected $email;

    /**
     * @var string  The avatar URI bound to the Identity.
     */
    protected $avatar;

    /**
     * @var array   The raw data about the Identity the Provider made available.
     */
    protected $raw;

    /**
     * @var array   The access tokens associated with the Identity.
     */
    protected $tokens = [];

    /**
     * Instantiates a Provider of the appropriate type specific to this Identity.
     *
     * Utility factory method for requesting consumer data when a specific Provider is needed, without having to
     * worry about the underlying implementation and protocols used to access that data.
     *
     * @param   string  $clientId       The consumer's (application/client) identifier.
     * @param   string  $clientSecret   The consumer's (application/client) secret.
     * @param   string  $redirectUrl    The consumer's (application/client) callback URL.
     * @return  interfaces\Provider     The created Provider instance.
     * @throws  \LogicException         When the Identity implementation did not define its static $provider property.
     */
    public static function provider(string $clientId, string $clientSecret, string $redirectUrl) : interfaces\Provider
    {
        if (!isset(static::$provider)) {
            throw new \LogicException('This identity does not appear to have a defined Provider class.');
        }

        return new static::$provider(new credentials\Client($clientId, $clientSecret, $redirectUrl));
    }

    /**
     * Creates a new Identity instance.
     *
     * @param   auth\interfaces\Token       $token  The access Token used to retrieve the data about the Identity.
     * @param   array                       $data   The raw data about the Identity the Provider made available.
     * @throws  \InvalidArgumentException           When the identifier was not mapped to the 'id' property beforehand
     *                                              and could not be found in the raw data either.
     */
    public function __construct(auth\interfaces\Token $token, array $data)
    {
        // Identifiers are required, all other data is optional.
        // The property check is put in place to allow child classes to map the identifier to the property
        // before calling the base constructor, in case the identifier is not available under the "id" key
        // in the raw data (and we don't want to rely on this convention globally).
        if (!isset($this->id)) {
            if (!isset($data['id'])) {
                throw new \InvalidArgumentException('The Identity is missing an unique identifier.');
            }

            $this->id = $data['id'];
        }

        $this->tokens[] = $token;
        $this->raw      = $data;
    }

    /**
     * {@inheritDoc}
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function getUsername() : ?string
    {
        return $this->username;
    }

    /**
     * {@inheritDoc}
     */
    public function getName() : ?string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getEmail() : ?string
    {
        return $this->email;
    }

    /**
     * {@inheritDoc}
     */
    public function getAvatar() : ?string
    {
        return $this->avatar;
    }

    /**
     * Returns the raw data about the Identity the Provider made available.
     *
     * @return  array
     */
    public function getRaw() : array
    {
        return $this->raw;
    }
}
