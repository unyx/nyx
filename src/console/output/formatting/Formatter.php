<?php namespace nyx\console\output\formatting;

/**
 * Output Formatter
 *
 * Responsible for processing strings, detecting formatting tags within them and applying the respective
 * Output Formatting Styles or removing any styling, while leaving any tags not matching the pattern unharmed.
 *
 * Styling can be applied either by:
 *  - defining Styles, whose name in turn becomes the name of the tag to use, ie. for a Style named 'error',
 *    in order to apply it to the string 'An error occurred!', you would write '<error>An error occurred!</error>';
 *  - defining inline styling, by using tags resembling inline styles in HTML tags, ie.:
 *    '<color: white; bg: red>An error occurred!</>'. The order is irrelevant (and whitespaces are optional),
 *    but it is necessary to specify 'color:' to define a foreground' color and 'bg:' for a background color.
 *    In order to apply additional options, you may use any other prefix. For instance '<weight: bold>Woohoo</>'
 *    will apply the 'bold' additional option (the word 'weight', however, has no meaning to the parser - it is
 *    nonetheless needed to match the pattern).
 *
 * Please {@see console\output\formatting\Style} to see which colors and additional options are supported.
 * ANSI support varies from system to system (and terminal to terminal) so use it with caution when portability
 * is one of your concerns.
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Formatter implements interfaces\Formatter
{
    /**
     * The formatting tag matching pattern.
     */
    protected const PATTERN = '[a-z][a-z0-9,_:;\s-]*+';

    /**
     * @var styles\Map      The Styles available to this Formatter.
     */
    private $styles;

    /**
     * @var styles\Stack    The style processing Stack for this Formatter.
     */
    private $stack;

    /**
     * Constructs a new Output Formatter.
     *
     * @param   styles\Map          $styles     A Map of Styles to be used instead of the default.
     * @param   interfaces\Style    $default    The default text styling.
     */
    public function __construct(styles\Map $styles = null, interfaces\Style $default = null)
    {
        $this->styles = $styles ?? $this->createDefaultStyles();
        $this->stack  = new styles\Stack($default);
    }

    /**
     * {@inheritdoc}
     */
    public function format(string $text, bool $decorated = true) : string
    {
        $out    = '';
        $offset = 0;

        preg_match_all("#<((".static::PATTERN.") | /(".static::PATTERN.")?)>#ix", $text, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[0] as $i => $match) {

            if (0 !== $match[1] && '\\' === $text[$match[1] - 1]) {
                continue;
            }

            $pos    = $match[1];
            $match  = $match[0];
            $out   .= $this->apply(substr($text, $offset, $pos - $offset), $decorated);
            $offset = $pos + strlen($match);

            // Check whether the second character is a slash, which would indicate that we are to close (pop)
            // the given tag.
            if ($opening = '/' !== $match[1]) {
                $tag = $matches[1][$i][0];
            } else {
                $tag = $matches[3][$i][0] ?? '';
            }

            // Inline styles will have empty closing tags, since they have no names (</>).
            // Empty closing tags will also work for non-inline styles however (closing the last stacked style
            // just the same).
            if (!$opening && !$tag) {
                $this->stack->pop();
                continue;
            }

            // See if the tag points to a Style name or contains inline formatting options and handle this
            // accordingly. If it does not refer to a Style, continue applying the topmost Style in the Stack.
            if (!$style = $this->handleFormattingTag($tag, $opening)) {
                $out .= $this->apply($text, $decorated);
            }
        }

        return $out . $this->apply(substr($text, $offset), $decorated);
    }

    /**
     * Returns the Styles in use by this Formatter.
     *
     * @return  styles\Map
     */
    public function getStyles() : styles\Map
    {
        return $this->styles;
    }

    /**
     * Returns the style processing Stack in use by this Formatter.
     *
     * @return  styles\Stack
     */
    public function getStack() : styles\Stack
    {
        return $this->stack;
    }

    /**
     * Creates a default Map of Styles to be used by this Formatter.
     *
     * @return  styles\Map
     */
    protected function createDefaultStyles() : styles\Map
    {
        return new styles\Map([
            'error'     => new Style('white', 'red'),
            'info'      => new Style('green'),
            'comment'   => new Style('cyan'),
            'important' => new Style('red'),
            'header'    => new Style('black', 'cyan')
        ]);
    }

    /**
     * Handles an encountered formatting tag by determining what Style it refers to (if applicable)
     * and pushing/popping the Style from the Stack, depending on whether it was an opening tag.
     *
     * @param   string              $tag        The encountered formatting tag.
     * @param   bool                $opening    Whether it was an opening tag.
     * @return  interfaces\Style                The Style matching the tag, if applicable.
     */
    protected function handleFormattingTag(string $tag, bool $opening) : ?interfaces\Style
    {
        // First attempt to grab a Style matching the tag from our Collection. If none is found,
        // see if it's an inline style.
        if ((!$style = $this->styles->get($tag)) && (!$style = Style::fromString($tag))) {
            return null;
        }

        if ($opening) {
            $this->stack->push($style);
        } else {
            $this->stack->pop($style);
        }

        return $style;
    }

    /**
     * Applies formatting to the given text.
     *
     * @param   string  $text       The text that should be formatted.
     * @param   bool    $decorated  Whether decorations, like colors, should be applied to the text.
     * @return  string              The resulting text.
     */
    protected function apply(string $text, bool $decorated) : string
    {
        return $decorated && !empty($text) ? $this->stack->current()->apply($text) : $text;
    }
}
