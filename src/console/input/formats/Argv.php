<?php namespace nyx\console\input\formats;

// Internal dependencies
use nyx\console;

/**
 * Argv Input
 *
 * Note: When passing an argv array yourself, ensure it does not contain the scriptname (when using $_SERVER['argv']
 * it will be present as the first element).
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Argv extends console\Input
{
    /**
     * @var interfaces\Parser   The token parser to be used.
     */
    private $parser;

    /**
     * Constructs a new Argv Input instance.
     *
     * @param   iterable            $parameters A sequence of parameters (in the argv format).
     * @param   interfaces\Parser   $parser     A Argv to Collections Lexer instance.
     */
    public function __construct(iterable $parameters = null, interfaces\Parser $parser = null)
    {
        // If no arguments were passed, let's use the globals returned by the CLI SAPI.
        if (!isset($parameters)) {
            $parameters = $_SERVER['argv'];

            // Strip the script name from the arguments.
            array_shift($parameters);
        }

        $this->parser = $parser;
        $this->raw    = $parameters instanceof interfaces\Tokens ? $parameters : new tokens\Argv($parameters);
    }

    /**
     * {@inheritdoc}
     */
    protected function parse() : console\Input
    {
        // Instantiate a Lexer if none has been defined yet.
        if (!isset($this->parser)) {
            $this->parser = new parsers\Argv;
        }

        // Fill our parameter value collections with the parsed input.
        $this->parser->parse($this->raw, $this->arguments(), $this->options());

        return $this;
    }
}
