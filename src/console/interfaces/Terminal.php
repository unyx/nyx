<?php namespace nyx\console\interfaces;

/**
 * Terminal Interface
 *
 * @package     Nyx\Console
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Terminal
{
    /**
     * Attempts to determine the width of the terminal the Application is being displayed in and return it. If
     * unable to determine the width, the method will return the given default value.
     *
     * @param   int     $default    The default value to return when unable to determine the width.
     * @return  int
     */
    public function getWidth(int $default = 80) : int;

    /**
     * Attempts to determine the height of the terminal the Application is being displayed in and return it. If
     * unable to determine the height, the method will return the given default value.
     *
     * @param   int     $default    The default value to return when unable to determine the height.
     * @return  int
     */
    public function getHeight(int $default = 32) : int;
}
