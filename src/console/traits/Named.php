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
 * The trait attempts to derive the delimiter in use if the trait is exhibited by a Command but it has no way of
 * doing this if it is used in another class tree in which case the validation rule might have to be modified
 * accordingly (a colon is assumed by default as disallowed delimiter character).
 *
 * @package     Nyx\Console\Application
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/console/index.html
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
    protected function validateName($name)
    {
        $delimiter = '\:';

        if (!preg_match('/^[^'.$delimiter.'\s]++$/', $name)) {
            throw new \InvalidArgumentException("A name [$name] must not be empty nor contain colons or whitespaces.");
        }
    }
}
