<?php namespace nyx\diagnostics\debug\dumpers;

// Internal dependencies
use nyx\diagnostics\debug\interfaces;

/**
 * DBug Dumper
 *
 * A bridge allowing to use dBug as a Dumper within the Debug subcomponent. Check out dBug itself on
 * Github at {@see https://github.com/ospinto/dBug}.
 *
 * Requires:
 * - Package: ospinto/dbug (available as suggestion for nyx/diagnostics within Composer)
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 * @todo        Readable breaks between each variable dump.
 */
class DBug implements interfaces\Dumper
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(...$vars)
    {
        // dBug isn't variadic so we need to adapt.
        foreach ($vars as $var) {
            new \dBug($var);
        }
    }
}
