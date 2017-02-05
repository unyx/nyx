<?php namespace nyx\diagnostics\debug\dumpers;

// Internal dependencies
use nyx\diagnostics\debug\interfaces;

/**
 * Kint Dumper
 *
 * A bridge allowing to use Kint as a Dumper within the Debug subcomponent. Check out Kint itself on
 * Github at {@see http://raveren.github.io/kint} and {@see https://github.com/raveren/kint}.
 *
 * Requires:
 * - Package: raveren/kint (available as suggestion for nyx/diagnostics within Composer)
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Kint implements interfaces\Dumper
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(...$vars)
    {
        call_user_func('Kint::dump', ...$vars);
    }
}
