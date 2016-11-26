<?php namespace nyx\auth\interfaces;

/**
 * Grant Interface
 *
 * A Grant within the scope of this component represents a method of acquiring access to resources, not the access
 * itself. It defines the rules and flow a client must follow in order to gain access/perform an action on a resource,
 * how and what kind of access is granted and the conditions of its revocation/expiry. The Grant is not necessarily
 * an implementation of the above - it is a representation of those rules and a means of identifying them.
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Grant
{
    /**
     * Returns the identifier of this type of a Grant.
     *
     * @return  string
     */
    public function getId() : string;
}
