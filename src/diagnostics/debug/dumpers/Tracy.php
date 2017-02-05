<?php namespace nyx\diagnostics\debug\dumpers;

// Internal dependencies
use nyx\diagnostics\debug\interfaces;

/**
 * Tracy Dumper
 *
 * A bridge allowing to use Tracy as a Dumper within the Debug subcomponent. Check out Tracy itself on
 * Github at {@see https://github.com/nette/tracy}.
 *
 * Requires:
 * - Package: tracy/tracy (available as suggestion for nyx/diagnostics within Composer)
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Tracy implements interfaces\Dumper
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(...$vars)
    {
        // Tracy isn't variadic so we need to adapt.
        foreach ($vars as $var) {
            \Tracy\Dumper::dump($var);
        }
    }
}
