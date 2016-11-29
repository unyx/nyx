<?php namespace nyx\console\terminals;

// Internal dependencies
use nyx\console;

/**
 * POSIX Terminal
 *
 * @package     Nyx\Console
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Posix extends console\Terminal
{
    /**
     * {@inheritDoc}
     */
    protected function getDimensions() : ?array
    {
        // Query stty first, then fall back to tput.
        if (null === $dimensions = $this->getDimensionsFromStty()) {
            $dimensions = $this->getDimensionsFromTput();
        }

        // If we got valid dimensions from any of our fallbacks, let's store the results.
        if (null !== $dimensions) {
            $this->dimensions = $dimensions;
        }

        return $this->dimensions;
    }

    /**
     * Executes a 'stty -a' call and parses the results in order to determine the dimensions of the terminal.
     *
     * @return  array   Either an array containing two keys - 'width' and 'height' or null if the data couldn't
     *                  be parsed to retrieve anything useful.
     */
    protected function getDimensionsFromStty() : ?array
    {
        if (empty($output = $this->execute('stty -a | grep columns'))) {
            return null;
        }

        if (preg_match('/rows.(\d+);.columns.(\d+);/i', $output, $matches) || preg_match('/;.(\d+).rows;.(\d+).columns/i', $output, $matches)) {
            return [
                'width' => (int) $matches[2],
                'height'=> (int) $matches[1]
            ];
        }

        return null;
    }

    /**
     * Executes a combined 'tput' call and parses the results in order to determine the dimensions of the terminal.
     *
     * @return  array   Either an array containing two keys - 'width' and 'height' or null if the data couldn't
     *                  be parsed to retrieve anything useful.
     */
    protected function getDimensionsFromTput() : ?array
    {
        if (empty($output = $this->execute('tput cols && tput lines'))) {
            return null;
        }

        // tput will have returned the values on separate lines, so let's just explode.
        $output = explode("\n", $output);

        return [
            'width' => (int) $output[0],
            'height'=> (int) $output[1]
        ];
    }
}
