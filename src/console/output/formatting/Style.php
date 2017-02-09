<?php namespace nyx\console\output\formatting;

/**
 * Output Formatting Style
 *
 * Used internally by the Output Formatter to apply the respective styles onto text within style tags,
 * but it can also be used to manually stylize strings.
 *
 * Note: The class does not check if the given strings already contain any sort of color codes or other escape
 * sequences. Applying a style onto an already decorated string can therefore yield unexpected results.
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Style implements interfaces\Style
{
    /**
     * @var array   The available foreground colors. The actual color representations may vary from the names as it
     *              depends on the terminal used to display them.
     */
    protected static $foregrounds = [
        'black'   => 30,
        'red'     => 31,
        'green'   => 32,
        'yellow'  => 33,
        'blue'    => 34,
        'magenta' => 35,
        'cyan'    => 36,
        'white'   => 37,
        'default' => 39
    ];

    /**
     * @var array   The available background colors.
     */
    protected static $backgrounds = [
        'black'   => 40,
        'red'     => 41,
        'green'   => 42,
        'yellow'  => 43,
        'blue'    => 44,
        'magenta' => 45,
        'cyan'    => 46,
        'white'   => 47,
        'default' => 49
    ];

    /**
     * @var array   The available additional emphasis options (Note: support for those depends on the type
     *              of the terminal used to display the application).
     */
    protected static $options = [
        'bold'       => [1, 22],
        'italic'     => [3, 23],
        'underscore' => [4, 24],
        'blink'      => [5, 25],
        'reverse'    => [7, 27],
        'conceal'    => [8, 28]
    ];

    /**
     * @var int     The currently set foreground color of this Style.
     */
    private $foreground;

    /**
     * @var int     The currently set background color of this Style.
     */
    private $background;

    /**
     * @var array   The currently set additional emphasis options of this Style.
     */
    private $emphasis = [];

    /**
     * Constructs a new Output Formatting Style.
     *
     * @param   string  $foreground     The foreground color to be set.
     * @param   string  $background     The background color to be set.
     * @param   array   $options        An array of additional options emphasis to be set.
     */
    public function __construct(?string $foreground, string $background = null, array $options = null)
    {
        if (isset($foreground)) {
            $this->setForeground($foreground);
        }

        if (isset($background)) {
            $this->setBackground($background);
        }

        if (isset($options)) {
            $this->setEmphasis($options);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setForeground(?string $color) : interfaces\Style
    {
        return $this->setColor('foreground', $color);
    }

    /**
     * {@inheritdoc}
     */
    public function setBackground(?string $color) : interfaces\Style
    {
        return $this->setColor('background', $color);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmphasis(array $options) : interfaces\Style
    {
        foreach ($options as $option) {
            if (!isset(static::$options[$option])) {
                throw new \InvalidArgumentException("The emphasis option [$option] is not recognized.");
            }

            // Only add the option when it is not already set.
            if (false === array_search($option, $this->emphasis)) {
                $this->emphasis[] = $option;
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(string $text) : string
    {
        $set   = [];
        $unset = [];

        if (isset($this->foreground)) {
            $set[]   = static::$foregrounds[$this->foreground];
            $unset[] = 39;
        }

        if (isset($this->background)) {
            $set[]   = static::$backgrounds[$this->background];
            $unset[] = 49;
        }

        if (!empty($this->emphasis)) {
            foreach ($this->emphasis as $option) {
                $set[]   = static::$options[$option][0];
                $unset[] = static::$options[$option][1];
            }
        }

        // Return the text wrapped by the appropriate escape sequences.
        return !empty($set)
            ? sprintf("\e[%sm%s\e[%sm", implode(';', $set), $text, implode(';', $unset))
            : $text;
    }

    /**
     * Sets a specific type of color on this Style.
     *
     * @param   string  $type   The type of the color to set.
     * @param   string  $color  The color to set.
     * @return  $this
     */
    protected function setColor(string $type, ?string $color) : interfaces\Style
    {
        if (isset($color) && !isset(static::${$type.'s'}[$color])) {
            throw new \InvalidArgumentException("The $type color [$color] is not recognized.");
        }

        $this->$type = $color;

        return $this;
    }
}
