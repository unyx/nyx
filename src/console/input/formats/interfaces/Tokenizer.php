<?php namespace nyx\console\input\formats\interfaces;

/**
 * Tokenizer Interface
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Tokenizer
{
    /**
     * Generates raw tokens from the given input.
     *
     * @param   mixed       $input  The input to tokenize. The exact type the input is expected to be in
     *                              depends on the concrete implementation.
     * @return  iterable
     */
    public function tokenize($input) : iterable;
}
