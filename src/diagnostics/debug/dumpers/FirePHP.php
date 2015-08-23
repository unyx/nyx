<?php namespace nyx\diagnostics\debug\dumpers;

// Vendor dependencies
use FirePHP as Base;

// Internal dependencies
use nyx\diagnostics\debug\interfaces;

/**
 * FirePHP Dumper
 *
 * A bridge allowing to use FirePHP as a Dumper within the Debug subcomponent. Check out FirePHP itself on
 * Github at {@see https://github.com/firephp/firephp-core}.
 *
 * Requires:
 * - Package: firephp/firephp-core (available as suggestion for nyx/diagnostics within Composer)
 *
 * @package     Nyx\Diagnostics\Debug
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/diagnostics/debug.html
 * @todo        Readable breaks between each variable dump.
 * @todo        Adjust the settings locally and apply them on each call to dump_r().
 */
class FirePHP implements interfaces\Dumper
{
    /**
     * @var Base    The underlying FirePHP instance.
     */
    private $dumper;

    /**
     * Constructs a new Dumper bridge for a FirePHP instance.
     *
     * @param   Dumper  $dumper     An already instantiated FirePHP instance or null to construct a new one
     *                              lazily upon the first call to self::dump().
     */
    public function __construct(Base $dumper = null)
    {
        $this->dumper = $dumper;
    }

    /**
     * {@inheritDoc}
     */
    public function dump(...$vars)
    {
        if (null === $this->dumper) {
            $this->dumper = Base::getInstance(false);
        }

        return call_user_func([$this->dumper, "fb"], ...$vars);
    }

    /**
     * Returns the underlying FirePHP instance.
     *
     * @return  Base|null
     */
    public function expose()
    {
        return $this->dumper;
    }
}
