<?php namespace nyx\diagnostics\debug\dumpers;

// Vendor dependencies
use Symfony\Component\VarDumper as base;

// Internal dependencies
use nyx\diagnostics\debug\interfaces;

/**
 * Symfony VarDumper Dumper
 *
 * A bridge allowing to use Symfony's VarDumper as a Dumper within the Debug subcomponent. Check out VarDumper
 * itself in Symfony's docs {@see http://symfony.com/doc/current/components/var_dumper/index.html}.
 *
 * Requires:
 * - Package: symfony/var-dumper (available as suggestion for nyx/diagnostics within Composer)
 *
 * @package     Nyx\Diagnostics\Debug
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/diagnostics/debug.html
 */
class VarDumper implements interfaces\Dumper
{
    /**
     * @var callable    The built proxy callable.
     */
    private $handler;

    /**
     * {@inheritDoc}
     */
    public function dump(...$vars)
    {
        if (null === $this->handler) {
            $cloner = new base\Cloner\VarCloner();
            $dumper = 'cli' === PHP_SAPI ? new base\Dumper\CliDumper : new base\Dumper\HtmlDumper;

            $this->handler = function ($var) use ($cloner, $dumper) {
                $dumper->dump($cloner->cloneVar($var));
            };
        }

        // VarDumper isn't variadic so we need to adapt.
        foreach ($vars as $var) {
            call_user_func($this->handler, $var);
        }
    }
}
