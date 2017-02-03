<?php namespace nyx\console\traits;

// External dependencies
use nyx\core;

/**
 * Named
 *
 * Any Named object within the Console component is assumed to be at some point called or accessed by the end-user
 * of the application. By assumption, in most cases that will be done from a command line. In order to keep a clear
 * distinction between Command/Suite/Application names and their arguments, no whitespaces nor command delimiters
 * are allowed within a name.
 *
 * @package     Nyx\Console
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
trait Named
{
    /**
     * Extending the core Named trait to define a naming convention inside the Console component.
     */
    use core\traits\Named;

    /**
     * {@inheritDoc}
     */
    protected function assertValidName(string $name) : string
    {
        $delimiter = '\:';

        if (!preg_match('/^[^'.$delimiter.'\s]++$/', $name)) {
            throw new \InvalidArgumentException("A name [$name] must not be empty nor contain colons or whitespaces.");
        }

        return $name;
    }
}
