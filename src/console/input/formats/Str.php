<?php namespace nyx\console\input\formats;

/**
 * String Input
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Str extends Argv
{
    /**
     * Constructs a String Input instance.
     *
     * @param   string                  $input      A string containing the arguments and options in an argv format.
     * @param   interfaces\Tokenizer    $tokenizer  A Tokenizer able to handle the $input string.
     * @param   interfaces\Parser       $parser     A Parser able to handle the tokens created by $tokenizer.
     */
    public function __construct(string $input, interfaces\Tokenizer $tokenizer = null, interfaces\Parser $parser = null)
    {
        // Unless a specific Tokenizer is given, we will instantiate a sane default.
        if (!isset($tokenizer)) {
            $tokenizer = new parsers\Str;

            // Our default Tokenizer is also a Parser, so if none was given, let's just re-use the instance.
            if (!isset($parser)) {
                $parser = $tokenizer;
            }
        }

        parent::__construct($tokenizer->tokenize($input), $parser);
    }
}
