<?php namespace nyx\diagnostics\debug\dumpers;

// Vendor dependencies
use NumberTwo\NumberTwo as Base;

// Internal dependencies
use nyx\diagnostics\debug\interfaces;

/**
 * NumberTwo Dumper
 *
 * A bridge allowing to use NumberTwo as a Dumper within the Debug subcomponent. Check out NumberTwo itself on
 * Github at {@see https://github.com/mnapoli/NumberTwo}.
 *
 * Requires:
 * - Package: mnapoli/number-two (available as suggestion for nyx/diagnostics within Composer)
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 * @todo        Readable breaks between each variable dump.
 * @todo        Adjust the settings locally and apply them on each call to the base dump().
 */
class NumberTwo implements interfaces\Dumper
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(...$vars)
    {
        // NumberTwo isn't variadic so we need to adapt.
        foreach ($vars as $var) {
            Base::dump($var);
        }
    }
}
