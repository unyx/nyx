<?php namespace nyx\console\input\values;

// Internal dependencies
use nyx\console\input;

/**
 * Input Parameter Multiple Values Definition
 *
 * @package     Nyx\Console\Input
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/console/index.html
 */
class Multiple extends input\Value
{
    /**
     * {@inheritDoc}
     *
     * @throws \LogicException  When the given $default value is not an array.
     */
    public function setDefault($default = null)
    {
        // Allow null, but otherwise require an array (with preferably actual default values).
        if (null !== $default && !is_array($default)) {
            throw new \LogicException("The default value for an argument accepting multiple values must be an array.");
        }

        parent::setDefault($default);
    }
}
