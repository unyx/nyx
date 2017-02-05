<?php namespace nyx\diagnostics\debug\interfaces;

/**
 * Dumper Interface
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Dumper
{
    /**
     * Dumps the given variable(s), providing information about their type, contents and others.
     * Accepts multiple values as parameters.
     *
     * @param   mixed   ...$vars    The variable(s) to dump info about.
     */
    public function __invoke(...$vars);
}
