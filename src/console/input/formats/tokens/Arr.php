<?php namespace nyx\console\input\formats\tokens;

// External dependencies
use nyx\core\collections;

// Internal dependencies
use nyx\console\input;

/**
 * Array Input Tokens
 *
 * This Tokens collection is able to resolve both Input Arguments and Options by name.
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Arr extends collections\Map implements input\formats\interfaces\Tokens
{
    /**
     * {@inheritDoc}
     */
    public function getArguments() : array
    {
        $arguments = [];

        foreach ($this->items as $name => $value) {
            if ($name[0] !== '-') {
                $arguments[$name] = $value;
            }
        }

        return $arguments;
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions() : array
    {
        $options = [];

        foreach ($this->items as $name => $value) {
            if ($name[0] === '-' && $name !== '--') {
                $options[$name] = $value;
            }
        }

        return $options;
    }
}
