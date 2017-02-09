<?php namespace nyx\console\output\formatting\styles;

// Internal dependencies
use nyx\console\output\formatting\interfaces;
use nyx\console\output\formatting;

/**
 * Output Formatting Styles Stack
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Stack
{
    /**
     * @var interfaces\Style[]   The Styles currently being processed.
     */
    private $styles = [];

    /**
     * @var interfaces\Style     The default Style used when the Stack is empty.
     */
    private $default;

    /**
     * Constructs a new Output Formatting Styles Stack.
     *
     * @param   interfaces\Style    $default    The default Style to be used when the Stack is empty.
     */
    public function __construct(interfaces\Style $default = null)
    {
        $this->default = $default ?? new formatting\Style(null);
    }

    /**
     * Pushes a style onto the Stack.
     *
     * @param   interfaces\Style    $style
     * @return  $this
     */
    public function push(interfaces\Style $style) : Stack
    {
        $this->styles[] = $style;

        return $this;
    }

    /**
     * Pops a style from the Stack.
     *
     * @param   interfaces\Style $searched          An optional, specific Style to pop from the Stack. If it is not
     *                                              the current element in the Stack, the Stack will be sliced and
     *                                              all Styles present after this instance will also be popped off.
     * @return  interfaces\Style                    The popped Style.
     * @throws  \InvalidArgumentException           When a Style was given but couldn't be found in the Stack.
     */
    public function pop(interfaces\Style $searched = null) : interfaces\Style
    {
        if (empty($this->styles)) {
            return $this->default;
        }

        if (!isset($searched)) {
            return array_pop($this->styles);
        }

        // Given a specific Style to search for, we need to compare the Styles to find the index at which
        // the Style resides, so that we can pop off the part of the Stack starting at the index.
        /* @var interfaces\Style $stackedStyle */
        foreach (array_reverse($this->styles, true) as $index => $stackedStyle) {
            // In order to support nested inline styles, we need to compare identity of the output,
            // not just of the instances.
            // @todo Strict equality of the instances may be sufficient depending on how the Formatter gets implemented.
            if ($searched->apply('') === $stackedStyle->apply('')) {
                $this->styles = array_slice($this->styles, 0, $index);

                return $stackedStyle;
            }
        }

        throw new \InvalidArgumentException('Encountered an incorrectly nested formatting style tag');
    }

    /**
     * Returns the current, topmost Style in the stack, or the default Style if none is stacked.
     *
     * @return  interfaces\Style
     */
    public function current() : interfaces\Style
    {
        return !empty($this->styles) ? end($this->styles) : $this->default;
    }

    /**
     * Empties the Stack.
     *
     * @return  $this
     */
    public function reset() : Stack
    {
        $this->styles = [];

        return $this;
    }

    /**
     * Returns the count of the Styles in the Stack, not taking the default Style into account.
     *
     * @return  int
     */
    public function count() : int
    {
        return count($this->styles);
    }

    /**
     * Sets the default Style to be used when the Stack is empty.
     *
     * @param   interfaces\Style $style
     * @return  $this
     */
    public function setDefault(interfaces\Style $style) : Stack
    {
        $this->default = $style;

        return $this;
    }

    /**
     * Returns the default Style used when the Stack is empty.
     *
     * @return  interfaces\Style
     */
    public function getDefault() : interfaces\Style
    {
        return $this->default;
    }
}
