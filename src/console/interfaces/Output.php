<?php namespace nyx\console\interfaces;

/**
 * Output Interface
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Output
{
    /**
     * The supported output formats.
     */
    public const
        DEFAULT = 1,
        RAW     = 2,
        PLAIN   = 4;

    /**
     * The supported output verbosity levels (ordered lowest to highest)
     */
    public const
        SILENT  = 32,
        NORMAL  = 64,
        VERBOSE = 128,
        LOUD    = 256,
        DEBUG   = 512;

    /**
     * Writes one or more messages to the output.
     *
     * @param   string|iterable     $messages   The message(s) to write.
     * @param   int                 $newlines   The number of newlines that should be appended after each message.
     * @param   int                 $options    A bitmask defining the format and verbosity of the message(s).
     * @return  $this
     */
    public function write($messages, int $newlines = 0, int $options = 0) : Output;

    /**
     * Writes one or more messages to the output and appends exactly one newline at the end of each message.
     *
     * @param   string|iterable     $messages   The message(s) to write.
     * @param   int                 $options    A bitmask defining the format and verbosity of the message(s).
     * @return  $this
     */
    public function line($messages, int $options = 0) : Output;

    /**
     * Sets whether the output can be decorated.
     *
     * @param   bool    $decorate   Whether to decorate the output or not.
     * @return  $this
     */
    public function setCanDecorate(bool $decorate) : Output;

    /**
     * Checks whether the Output can be decorated.
     *
     * @return  bool
     */
    public function canDecorate() : bool;

    /**
     * Returns the current verbosity level of the output.
     *
     * @return  int
     */
    public function getVerbosity() : int;

    /**
     * Sets the maximal verbosity level of the output. Any messages above this level will silently get discarded
     * without being written to the underlying output.
     *
     * @param   int     $level  The level of verbosity.
     * @return  $this
     */
    public function setVerbosity(int $level) : Output;

    /**
     * Checks whether output is silenced.
     *
     * @return  bool
     */
    public function isSilenced() : bool;

    /**
     * Checks whether the verbosity of the output is set to be at least verbose.
     *
     * @return  bool
     */
    public function isVerbose() : bool;

    /**
     * Checks whether the verbosity of the output is set to be at least loud.
     *
     * @return  bool
     */
    public function isLoud() : bool;

    /**
     * Checks whether the verbosity of the output is set to be at least on a debug-level.
     *
     * @return  bool
     */
    public function isDebug() : bool;
}
