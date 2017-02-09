<?php namespace nyx\console\input\formats\parsers;

// External dependencies
use nyx\core;

// Internal dependencies
use nyx\console\input\formats;

/**
 * String Parser
 *
 * The input string is expected to be formatted according to GNU guidelines (same as PHP's internal argv handling).
 * @see https://www.gnu.org/software/guile/manual/html_node/Command-Line-Format.html for more information.
 *
 * This class uses an algorithm ported from Symfony's Console, written by (c) Fabien Potencier <fabien@symfony.com>
 *
 * @version     0.1.0
 * @author      Fabien Potencier <fabien@symfony.com>
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Str extends Argv implements formats\interfaces\Tokenizer
{
    /**
     * The token matching expressions.
     */
    const PATTERN_BARE   = '([^\s]+?)(?:\s|(?<!\\\\)"|(?<!\\\\)\'|$)';
    const PATTERN_QUOTED = '(?:"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"|\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\')';

    /**
     * {@inheritdoc}
     *
     * @param   string  $input
     * @throws  core\exceptions\InvalidArgumentType When the given input is not a string.
     * @throws  \RuntimeException                   Upon failing to parse a given substring.
     */
    public function tokenize($input) : iterable
    {
        if (!is_string($input)) {
            throw new core\exceptions\InvalidArgumentType($input, ['string']);
        }

        // Change line breaks, tabs etc. into whitespaces.
        $length = strlen($input);
        $cursor = 0;
        $i      = 0;
        $tokens = new formats\tokens\Argv;

        while ($cursor < $length) {
            if (preg_match('/\s+/A', $input, $match, null, $cursor)) {
            } elseif (preg_match('/([^="\'\s]+?)(=?)('.static::PATTERN_QUOTED.'+)/A', $input, $match, null, $cursor)) {
                $tokens->set($i++, $match[1].$match[2].stripcslashes(str_replace(['"\'', '\'"', '\'\'', '""'], '', substr($match[3], 1, -1))));
            } elseif (preg_match('/'.static::PATTERN_QUOTED.'/A', $input, $match, null, $cursor)) {
                $tokens->set($i++, stripcslashes(substr($match[0], 1, -1)));
            } elseif (preg_match('/'.static::PATTERN_BARE.'/A', $input, $match, null, $cursor)) {
                $tokens->set($i++, stripcslashes($match[1]));
            } else {
                throw new \RuntimeException('Failed to parse string near "... '.substr($input, $cursor, 10).' ..."');
            }

            $cursor += strlen($match[0]);
        }

        return $tokens;
    }
}
