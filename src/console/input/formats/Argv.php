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
     * @var lexers\ArgvToCollections    The token parser to be used.
     */
    private $lexer;

    /**
     * Constructs a new Argv Input instance.
     *
     * @param   array                       $parameters An array of parameters (in the argv format).
     * @param   lexers\ArgvToCollections    $lexer      A Argv to Collections Lexer instance.
     */
    public function __construct(array $parameters = null, lexers\ArgvToCollections $lexer = null)
    {
        // If no arguments were passed, let's use the globals returned by the CLI SAPI.
        if (!isset($parameters)) {
            $parameters = $_SERVER['argv'];

            // Strip the script name from the arguments.
            array_shift($parameters);
        }

        $this->lexer = $lexer;
        $this->raw   = new tokens\Argv($parameters);
    }

    /**
     * {@inheritdoc}
     */
    protected function parse() : console\Input
    {
        // Instantiate a Lexer if none has been defined yet.
        if (!isset($this->lexer)) {
            $this->lexer = new lexers\ArgvToCollections;
        }

        // Fill our parameter value collections with the parsed input.
        $this->lexer->fill($this->arguments(), $this->options(), $this->raw);

        return $this;
    }
}
