<?php namespace nyx\console\interfaces\input;

// External dependencies
use nyx\core\collections;

/**
 * Input Tokens Interface
 *
 * Represents a Collection of unparsed tokens of a known format/structure which have their raw formatting preserved.
 * Implementations of this interface must be able to distinguish Input Options from Arguments in the collection,
 * provided they are given a collection of tokens in a format recognized by them.
 *
 * This interface does not dictate the type of Collection that needs to be implemented nor does it require
 * the tokens to be in immutable sequence order.
 *
 * @package     Nyx\Console
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Tokens extends collections\interfaces\Collection
{
    /**
     * Returns all tokens which do not start with a hyphen and therefore *appear to* be arguments.
     *
     * @return  array
     */
    public function arguments() : array;

    /**
     * Returns all tokens which start with a hyphen and therefore *appear to* be options, without
     * differentiating between short and long options.
     *
     * @return  array
     */
    public function options() : array;
}
