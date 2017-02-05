<?php namespace nyx\diagnostics\debug\dumpers;

// Vendor dependencies
use Ladybug\Dumper;

// Internal dependencies
use nyx\diagnostics\debug\interfaces;

/**
 * Ladybug Dumper
 *
 * A bridge allowing to use Ladybug as a Dumper within the Debug subcomponent. The class also gives you access
 * to the underlying Ladybug\Dumper instance if you want to customize its settings. Check out Ladybug itself on
 * Github at {@see https://github.com/raulfraile/Ladybug}.
 *
 * Requires:
 * - Package: raulfraile/ladybug (available as suggestion for nyx/diagnostics within Composer)
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Ladybug implements interfaces\Dumper
{
    /**
     * @var Dumper  The underlying instance of the Ladybug\Dumper.
     */
    private $dumper;

    /**
     * Constructs a new Dumper bridge for an Ladybug\Dumper instance.
     *
     * @param   Dumper  $dumper     An already instantiated Ladybug\Dumper instance or null to construct a new one.
     */
    public function __construct(Dumper $dumper = null)
    {
        $this->dumper = $dumper ?: new Dumper;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(...$vars)
    {
        // @todo Actual output handling.
        echo call_user_func([$this->dumper, "dump"], ...$vars);
    }

    /**
     * Returns the underlying Ladybug\Dumper instance.
     *
     * @return  Dumper
     * @todo    Rename to getLadybug or...?
     */
    public function expose()
    {
        return $this->dumper;
    }
}
