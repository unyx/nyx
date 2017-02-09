<?php namespace nyx\console\output\formatting\interfaces;

/**
 * Output Formatting Style Interface
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Style
{
    /**
     * Sets the foreground color of this Style.
     *
     * @param   string|null     $color      The name of the color.
     * @throws  \InvalidArgumentException   When the given color is not available.
     * @return  $this
     */
    public function setForeground(?string $color) : Style;

    /**
     * Sets the background color of this Style.
     *
     * @param   string|null     $color      The name of the color.
     * @throws  \InvalidArgumentException   When the given color is not available.
     * @return  $this
     */
    public function setBackground(?string $color) : Style;

    /**
     * Sets one or more emphasis option(s) for this Style.
     *
     * @param   array    $options           An array of emphasis option names.
     * @throws  \InvalidArgumentException   When one of the given emphasis options is not available.
     * @return  $this
     */
    public function setEmphasis(array $options) : Style;

    /**
     * Applies this Style to a given string.
     *
     * @param   string  $text   The string to be styled.
     * @return  string          The stylized string.
     */
    public function apply(string $text) : string;
}
