<?php namespace nyx\auth\id\interfaces;

/**
 * Identity Interface
 *
 * The availability of Identity data is not enforced. The only exception to this is the (preferably unique) identifier
 * of the Identity.
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Identity
{
    /**
     * Returns the unique identifier of the Identity specific to its Provider.
     *
     * @return  string
     */
    public function getId() : string;

    /**
     * Returns the username/nickname/display name bound to the Identity.
     *
     * @return  string|null
     */
    public function getUsername() : ?string;

    /**
     * Returns the full name bound to the Identity.
     *
     * @return  string|null
     */
    public function getName() : ?string;

    /**
     * Returns the e-mail address bound to the Identity.
     *
     * @return  string|null
     */
    public function getEmail() : ?string;

    /**
     * Returns the avatar URI bound to the Identity.
     *
     * @return  string|null
     */
    public function getAvatar() : ?string;
}
