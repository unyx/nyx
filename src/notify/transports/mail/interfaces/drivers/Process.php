<?php namespace nyx\notify\transports\mail\interfaces\drivers;

// Internal dependencies
use nyx\notify\transports\mail\interfaces;

/**
 * Process Mail Driver Interface
 *
 * A Driver that requires manual starting and stopping before and after transactions, which may be costly
 * performance-wise and requires special consideration.
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Process extends interfaces\Driver
{
    /**
     * Starts the Driver's process.
     */
    public function start();

    /**
     * Stops the Driver's process.
     */
    public function stop();

    /**
     * Checks whether the Driver has been started and is currently running.
     *
     * @return  bool
     */
    public function isStarted();
}
