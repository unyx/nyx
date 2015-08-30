<?php namespace nyx\console\interfaces\input;

// External dependencies
use nyx\core\collections;

/**
 * Input Tokens Interface
 *
 * @package     Nyx\Console\Input
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/console/index.html
 */
interface Tokens extends collections\interfaces\Collection
{
    /**
     * Prepends a token to the collection.
     *
     * @param   string  $value  The value to prepend.
     * @return  $this
     */
    public function prepend(string $value) : static;

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
