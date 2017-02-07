<?php namespace nyx\console\input\formats\tokens;

// External dependencies
use nyx\core;

// Internal dependencies
use nyx\console\input;

/**
 * Argv Input Tokens
 *
 * This Tokens collection is able to resolve Input Options by name, as the name is contained in the argv data,
 * but will not be able to do the same for Input Arguments, as their names are unknown until the input gets
 * bound to a Definition.
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Argv extends core\collections\Map implements input\formats\interfaces\Tokens
{
    /**
     * {@inheritdoc}
     */
    public function get($parameter, $default = null)
    {
        foreach ($this->items as $token) {

            if ($token === $parameter || 0 === strpos($token, $parameter . '=')) {
                if (false !== $pos = strpos($parameter, '=')) {
                    return substr($token, $pos + 1);
                }

                return $token;
            }
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function has($parameter) : bool
    {
        return in_array($parameter, $this->items);
    }


    /**
     * {@inheritdoc}
     */
    public function remove($parameter) : core\collections\interfaces\Map
    {
        if (false !== $key = array_search($parameter, $this->items)) {
            unset($this->items[$key]);
        }

        return $this;
    }


    /**
     * {@inheritDoc}
     */
    public function getArguments() : array
    {
        $arguments = [];

        foreach ($this->items as $token) {
            if ($token[0] !== '-') {
                $arguments[] = $token;
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

        foreach ($this->items as $token) {
            if ($token[0] === '-' && $token !== '--') {
                $options[] = $token;
            }
        }

        return $options;
    }
}
