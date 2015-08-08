<?php namespace nyx\core;

/**
 * Mask
 *
 * Base class for concrete fields/objects utilizing bitmasks (permissions, statuses etc.). This could also be used
 * as a generic mask builder.
 *
 * @package     Nyx\Core
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/core/index.html
 */
class Mask
{
    /**
     * @var int The current bitmask.
     */
    private $mask;

    /**
     * Constructs a new Mask instance.
     *
     * @param   int $mask   The bitmask to start with.
     */
    public function __construct(int $mask = 0)
    {
        $this->mask = $mask;
    }

    /**
     * Returns the current bitmask.
     *
     * @return  int
     */
    public function get() : int
    {
        return $this->mask;
    }

    /**
     * Checks if the given bits are set in the mask.
     *
     * @param   int     $mask
     * @return  bool
     */
    public function is(int $mask) : bool
    {
        return ($this->mask & $mask) === $mask;
    }

    /**
     * Sets the given bits in the mask.
     *
     * @param   int     $mask
     * @return  $this
     */
    public function set(int $mask) : self
    {
        $this->mask |= $mask;

        return $this;
    }

    /**
     * Removes the given bits from the mask.
     *
     * @param   int     $mask
     * @return  $this
     */
    public function remove(int $mask) : self
    {
        $this->mask &= ~$mask;

        return $this;
    }

    /**
     * Resets the mask to a state with no bits set.
     *
     * @return  $this
     */
    public function reset() : self
    {
        $this->mask = 0;

        return $this;
    }
}
