<?php namespace nyx\diagnostics\debug\dumpers;

// Vendor dependencies
use dump_r\Core;

// Internal dependencies
use nyx\diagnostics\debug\interfaces;

/**
 * Dump_r Dumper
 *
 * A bridge allowing to use Dump_r as a Dumper within the Debug subcomponent. Check out Dump_r itself on
 * Github at {@see https://github.com/leeoniya/dump_r.php}.
 *
 * Requires:
 * - Package: leeoniya/dump-r (available as suggestion for nyx/diagnostics within Composer)
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 * @todo        Readable breaks between each variable dump.
 * @todo        Adjust the settings locally and apply them on each call to dump_r().
 */
class Dump_r implements interfaces\Dumper
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(...$vars)
    {
        // Dump_r isn't variadic so we need to adapt.
        foreach ($vars as $var) {
            Core::dump_r($var);
        }
    }
}
