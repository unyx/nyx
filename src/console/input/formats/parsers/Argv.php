<?php namespace nyx\console\input\formats\parsers;

// Internal dependencies
use nyx\console\input\formats\tokens;
use nyx\console\input\formats\interfaces;
use nyx\console\input\parameter\values;
use nyx\console\input\parameter\definitions;
use nyx\console\input;

/**
 * Argv to Collections Lexer
 *
 * Takes an Argv Tokens instance and populates the given Arguments and Options collections based on the
 * data contained in it.
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Argv implements interfaces\Parser
{
    /**
     * Fill the given Input Arguments and Options based on their definitions and the Argv input given.
     *
     * @param   values\Arguments    $arguments  The Input Arguments to fill.
     * @param   values\Options      $options    The Input Options to fill.
     * @param   tokens\Argv         $input      The Argv tokens to be parsed.
     */
    public function parse(interfaces\Tokens $input, values\Arguments $arguments, values\Options $options) : void
    {
        $isParsingOptions = true;

        // Grab all tokens. Can't iterate directly over the Collection as we need to easily determine
        // which tokens are left to be processed (thus array_shift() below).
        $input = $input->all();

        // Loop through the input given.
        while (null !== $token = array_shift($input)) {

            if ($isParsingOptions) {
                // When '--' is present as a token, it forces us to treat
                // everything following it as arguments.
                if ('--' === $token) {
                    $isParsingOptions = false;
                    continue;
                }

                // Full options (token beginning with two hyphens).
                if (0 === strpos($token, '--')) {
                    $this->parseLongOption($token, $options, $input);
                    continue;
                }

                // Shortcuts (token beginning with exactly one hyphen).
                if ('-' === $token[0]) {
                    $this->parseShortOption($token, $options, $input);
                    continue;
                }
            }

            // In any other case treat the token as an argument.
            $arguments->push($token);
        }
    }

    /**
     * Parses the given token as a long option and adds it to the Options set.
     *
     * @param   string          $token      The token to parse.
     * @param   values\Options  $options    The collection of Options being filled.
     * @param   array&          $input      A reference to the argv input array.
     */
    protected function parseLongOption(string $token, values\Options $options, array& $input) : void
    {
        // Remove the two starting hyphens.
        $name  = substr($token, 2);
        $value = null;

        // If the token contains a value for the option, we need to split the token accordingly.
        if (false !== $pos = strpos($name, '=')) {
            $value = substr($name, $pos + 1);
            $name  = substr($name, 0, $pos);
        }

        // If the option didn't have a value assigned using the 'equals' sign, the value may be contained in
        // the next token, *if* the Option accepts values to begin with.
        // Note: It's possible that no Definition for the given Option even exists, but we're deferring
        // Exception throwing to parameters\Options::set() to avoid some duplicate code.
        /* @var input\Option $definition */
        if (!isset($value) && $definition = $options->definitions()->get($name) and $definition->hasValue()) {
            // $input[0][0] is the first character of the first token in the $input array.
            // Ensure the token does not begin with a hyphen, which would indicate it's another Option,
            // and not a value for the Option we are processing.
            if (isset($input[0]) && '-' !== $input[0][0]) {
                $value = array_shift($input);
            }
        }

        $options->set($name, $value);
    }

    /**
     * Parses the given token as a short option and adds it to the Options set.
     *
     * @param   string          $token      The token to parse.
     * @param   values\Options  $options    The collection of Options being filled.
     * @param   array&          $input      A reference to the argv input array.
     */
    protected function parseShortOption($token, values\Options $options, array& $input) : void
    {
        // Remove the starting hyphen.
        // If it's just a hyphen and nothing else, ignore it since it's most likely a mistype.
        if (empty($shortcut = substr($token, 1))) {
            return;
        }

        foreach ($this->resolveShortOption($shortcut, $options) as $name => $value) {
            $options->set($name, $value);
        }
    }

    /**
     * Takes a short option or short option set (without the starting hyphen) and resolves it to full options
     * and their values, depending on the Definitions given.
     *
     * @param   string          $shortcut       The short option(s) to resolve.
     * @param   values\Options  $options        The Input Options being filled.
     * @return  array                           A map of full option $names => $values.
     */
    private function resolveShortOption(string $shortcut, values\Options $options) : array
    {
        /* @var $definitions definitions\Options */
        $definitions = $options->definitions();

        // We can return right here if the shortcut is a single character.
        if (1 === $length = strlen($shortcut)) {
            return [$definitions->getByShortcut($shortcut)->getName() => null];
        }

        // We have more than one character. However, if the first character points to an option that accepts values,
        // we will treat all characters afterwards as a value for said option.
        if ($definition = $definitions->getByShortcut($shortcut[0]) and $definition->hasValue()) {
            // First, remove the shortcut from the string to leave us only with the value. Also, if the actual value
            // starts with "=", we're going to remove that character (ie. the two first characters instead of just the
            // shortcut) to cover bad syntax.
            return [$definition->getName() => substr($shortcut, strpos($shortcut, '=') === 1 ? 2 : 1)];
        }

        // At this point consider the whole string as a set of different options.
        return $this->resolveShortOptionSet($shortcut, $definitions);
    }

    /**
     * Takes a set of short options contained in a string and resolves them to full options and their
     * values, depending on the Definitions given.
     *
     * @param   string                  $shortcut       The short option(s) to resolve.
     * @param   definitions\Options     $definitions    The Options Definition.
     * @return  array                                   An array of full option $names => $values.
     */
    private function resolveShortOptionSet(string $shortcut, definitions\Options $definitions) : array
    {
        $length = strlen($shortcut);
        $result = [];

        // Loop through the string, character by character.
        for ($i = 0; $i < $length; $i++) {
            $definition = $definitions->getByShortcut($shortcut[$i]);

            // The last shortcut in a set may have a value appended afterwards.
            if ($definition->hasValue()) {
                $result[$definition->getName()] = $i === $length - 1 ? null : substr($shortcut, $i + 1);
                break;
            }

            $result[$definition->getName()] = null;
        }

        return $result;
    }
}
