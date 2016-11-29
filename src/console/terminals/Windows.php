<?php namespace nyx\console\terminals;

// Internal dependencies
use nyx\console;

/**
 * Windows Terminal
 *
 * @package     Nyx\Console
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Windows extends console\Terminal
{
    /**
     * {@inheritDoc}
     */
    protected function getDimensions() : ?array
    {
        // Ansicon environmental variable is our first fallback. Result of 'mode CON' is the next one.
        if (null === $dimensions = $this->getDimensionsFromAnsicon()) {
            $dimensions = $this->getDimensionsFromMode();
        }

        // If we got valid dimensions from any of our fallbacks, let's store the results.
        if (null !== $dimensions) {
            $this->dimensions = $dimensions;
        }

        return $this->dimensions;
    }

    /**
     * Attempts to determine the Terminal's dimensions based on the 'ANSICON' environmental variables.
     *
     * @return  array   Either an array containing two keys - 'width' and 'height' or null if the data couldn't
     *                  be parsed to retrieve anything useful.
     */
    protected function getDimensionsFromAnsicon() : ?array
    {
        if (preg_match('/^(\d+)x(\d+)(?: \((\d+)x(\d+)\))?$/', trim(getenv('ANSICON')), $matches)) {
            return [
                'width'  => (int) $matches[1],
                'height' => (int) ($matches[4] ?? $matches[2])
            ];
        }

        return null;
    }

    /**
     * Attempts to determine the Terminal's dimensions based on the result of a 'mode CON' system call.
     *
     * @return  array   Either an array containing two keys - 'width' and 'height' or null if the data couldn't
     *                  be parsed to retrieve anything useful.
     */
    protected function getDimensionsFromMode() : ?array
    {
        if (empty($output = $this->execute('mode CON'))) {
            return null;
        }

        if (preg_match('/--------+\r?\n.+?(\d+)\r?\n.+?(\d+)\r?\n/', $output, $matches)) {
            return [
                'width' => (int) $matches[2],
                'height'=> (int) $matches[1]
            ];
        }

        return null;
    }
}
