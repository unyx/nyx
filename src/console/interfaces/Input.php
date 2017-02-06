<?php namespace nyx\console\interfaces;

// Internal dependencies
use nyx\console;

/**
 * Input Interface
 *
 * Wraps access to Input Parameters (Arguments and Options). However, only the raw (unmapped and not validated)
 * input tokens are available until the instance gets bound to a Definition.
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Input
{
    /**
     * Binds the Input to the given Definition, effectively converting raw input into named parameters,
     * validating them and executing their binding callbacks if applicable.
     *
     * @param   console\input\Definition    $definition     An input Definition instance.
     * @param   console\Context             $context        The Execution Context the binding shall be performed in.
     * @return  $this
     */
    public function bind(console\input\Definition $definition, console\Context $context) : Input;

    /**
     * Returns the Input Tokens instance containing the raw input.
     *
     * @return  input\Tokens
     */
    public function raw() : input\Tokens;

    /**
     * Returns the Input Arguments collection.
     *
     * @return  console\input\parameter\values\Arguments
     */
    public function arguments() : ?console\input\parameter\values\Arguments;

    /**
     * Returns the Input Options collection.
     *
     * @return  console\input\parameter\values\Options
     */
    public function options() : ?console\input\parameter\values\Options;
}
