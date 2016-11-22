<?php namespace nyx\auth\interfaces;

/**
 * Credentials Interface
 *
 * An extension of the Token interface, Credentials in the "auth" context should be thought of
 * as pairs of identifiers and secrets. In the probably most common use case, a user's login would
 * be the identifier in said pair, while his password would represent the secret.
 *
 * Although the existence of a "secret" is not enforced on the interface level, implementations
 * may enforce it.
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Credentials extends Token
{
    /**
     * Returns the associated "secret" value of the Credentials pair, if available.
     *
     * @return  string|null
     */
    public function getSecret() : ?string;
}
