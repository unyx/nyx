<?php namespace nyx\connect\streams\interfaces;

// Vendor dependencies
use Psr;

// External dependencies
use nyx\core;

/**
 * Stream Interface
 *
 * @package     Nyx\Connect
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/connect/index.html
 */
interface Stream extends Psr\Http\Message\StreamInterface, core\interfaces\Stringable
{
    /**
     * Status/mode bits of the Stream.
     */
    const LOCAL    = 1;
    const READABLE = 2;
    const WRITABLE = 4;
    const SEEKABLE = 8;
    const BLOCKED  = 16;

    /**
     * Reads a line of data from the underlying stream up to the given length of bytes.
     *
     * @param   int             $length     The amount of bytes that should be read.
     * @return  string                      The data read from the stream,
     * @throws  \RuntimeException           On failure (inability to read from the stream or reaching EOF).
     */
    public function line(int $length = null) : string;
}
