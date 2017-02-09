<?php namespace nyx\console\input\formats;

// Internal dependencies
use nyx\console;

/**
 * Array Input
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Arr extends console\Input
{
    /**
     * Constructs a new Array Input instance.
     *
     * @param   iterable    $parameters     A map of input parameters (name => value).
     */
    public function __construct(iterable $parameters)
    {
        $this->raw = $parameters instanceof interfaces\Tokens ? $parameters : new tokens\Arr($parameters);
    }

    /**
     * {@inheritdoc}
     */
    protected function parse() : console\Input
    {
        foreach ($this->raw as $name => $value) {
            if (0 === strpos($name, '--')) {
                $this->options()->set(substr($name, 2), $value);
            } elseif ('-' === $name[0]) {
                $this->options()->set(substr($name, 1), $value);
            } else {
                $this->arguments()->set($name, $value);
            }
        }

        return $this;
    }
}
