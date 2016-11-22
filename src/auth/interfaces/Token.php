<?php namespace nyx\auth\interfaces;

// External dependencies
use nyx\core;

/**
 * Token Interface
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Token extends core\interfaces\Serializable
{
    /**
     * Returns the Token's identifier (ie. the Token's underlying value).
     *
     * @return  string
     */
    public function getId() : string;

    /**
     * Determines whether this Token matches another Token. Only equality is being determined, not identity.
     *
     * @param   Token   $that   The other Token to compare this one against.
     * @return  bool            True when this Token equals the given Token, false otherwise.
     */
    public function matches(Token $that) : bool;
}
