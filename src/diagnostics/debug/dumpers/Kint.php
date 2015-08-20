<?php namespace nyx\diagnostics\debug\dumpers;

// Internal dependencies
use nyx\diagnostics\debug;

/**
 * Kint Dumper
 *
 * A bridge allowing to use Kint as a Dumper within the Debug subcomponent. Check out Kint itself on
 * Github at {@see http://raveren.github.io/kint} and {@see https://github.com/raveren/kint}.
 *
 * Requires:
 * - Package: raveren/kint (available as suggestion for nyx/diagnostics within Composer)
 *
 * @package     Nyx\Diagnostics\Debug
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/diagnostics/debug.html
 */
class Kint implements debug\interfaces\Dumper
{
    /**
     * {@inheritDoc}
     */
    public function dump(...$vars)
    {
        call_user_func_array('Kint::dump', $vars);
    }
}
