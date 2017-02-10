<?php namespace nyx\console\output\formatting\interfaces;

/**
 * Output Formatter Interface
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Formatter
{
    /**
     * Formats the given text according to the behaviour of the Formatter.
     *
     * @param   string  $text       The text that should be formatted.
     * @param   bool    $decorated  Whether decorations, like colors, should be applied to the text.
     *                              When set to false, formatting artifacts (like styling tags) will be removed
     *                              from the text without applying them.
     * @return  string              The resulting text.
     */
    public function format(string $text, bool $decorated = true) : string;
}
