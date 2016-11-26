<?php namespace nyx\auth\interfaces;

/**
 * Access Scope Interface
 *
 * Implementations of this interface MUST be immutable.
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Scope
{
    /**
     * Returns the identifier of this Access Scope.
     *
     * @return  string
     */
    public function getId() : string;
}
