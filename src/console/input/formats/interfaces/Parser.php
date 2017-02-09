<?php namespace nyx\console\input\formats\interfaces;

// Internal dependencies
use nyx\console\input\parameter\values;

/**
 * Parser Interface
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Parser
{
    /**
     * Parses the given Tokens into Input Arguments and Options based on their definitions.
     *
     * @param   Tokens              $input      The Tokens to be parsed.
     * @param   values\Arguments    $arguments  The Input Arguments to fill.
     * @param   values\Options      $options    The Input Options to fill.
     */
    public function parse(Tokens $input, values\Arguments $arguments, values\Options $options) : void;
}
