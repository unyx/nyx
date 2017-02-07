<?php namespace nyx\console\input\formats\interfaces;

// External dependencies
use nyx\core\collections;

/**
 * Input Tokens Interface
 *
 * Represents a Collection of unparsed tokens of a known format/structure which have their raw formatting preserved.
 * Implementations of this interface must be able to distinguish Input Options from Arguments in the collection,
 * provided they are given a collection of tokens in a format recognized by them.
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Tokens extends collections\interfaces\Map
{
    /**
     * Returns all tokens which *appear to* be arguments.
     *
     * @return  array
     */
    public function getArguments() : array;

    /**
     * Returns all tokens which *appear to* be options, without differentiating between short and long options.
     *
     * @return  array
     */
    public function getOptions() : array;
}
