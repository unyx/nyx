<?php namespace nyx\diagnostics\debug;

// External dependencies
use nyx\core\collections;

/**
 * Trace
 *
 * Sequence for \Exception::getTrace() frames which get converted into diagnostic\Frame instances when passed
 * to the constructor. The collection *is* mutable - the frames are numerically indexed (per assumption, in the
 * order returned by getTrace(), but other uses are obviously possible).
 *
 * Please refer to {@see core\collections\Sequence} and the corresponding trait for details on which methods are
 * available for Collections. This class only overrides those which might directly inject elements into the Collection,
 * in order to ensure proper types. Some methods (eg. map, filter) will return new Trace instances with the results
 * of their calls, and ultimately the constructor of a Frame will take care of type checks and only use the data
 * it knows about.
 *
 * @package     Nyx\Diagnostics\Debug
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/diagnostics/debug.html
 */
class Trace extends collections\Sequence
{
    /**
     * {@inheritDoc}
     */
    public function push($frame) : self
    {
        return parent::push($this->assertValueIsFrame($frame));
    }

    /**
     * {@inheritDoc}
     */
    public function prepend($frame) : self
    {
        return parent::prepend($this->assertValueIsFrame($frame));
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $index, $frame) : self
    {
        return parent::update($index, $this->assertValueIsFrame($frame));
    }

    /**
     * {@inheritDoc}
     */
    public function contains($item) : bool
    {
        // Avoid some overhead on simple type mismatches.
        if (!$item instanceof Frame) {
            return false;
        }

        return parent::contains($item);
    }

    /**
     * Attempts to convert the given value to a Frame instance (if it isn't one already) and returns it if possible.
     *
     * @param   mixed   $value              The value to check.
     * @return  Frame
     * @throws  \InvalidArgumentException   When the given value is not a frame and could not be converted to one.
     */
    protected function assertValueIsFrame($value)
    {
        if ($value instanceof Frame) {
            return $value;
        }

        if (is_array($value)) {
            return new Frame($value);
        }

        throw new \InvalidArgumentException('The given value is not a Frame instance and could not be converted to one.');
    }
}
