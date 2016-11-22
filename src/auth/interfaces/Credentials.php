<?php namespace nyx\auth\interfaces;

/**
 * Credentials Interface
 *
 * An extension of the Token interface, Credentials in the "auth" context should be thought of
 * as pairs of identifiers and secrets. In the probably most common use case, a user's login would
 * be the identifier in said pair, while the secret would be represented by his password.
 *
 * The secret itself can be a nested Credentials instance. Implementations need to account for that.
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
     * @return  string|Credentials
     */
    public function getSecret();
}
