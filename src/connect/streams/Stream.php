<?php namespace nyx\connect\streams;

// External dependencies
use nyx\core;

/**
 * Stream
 *
 * @package     Nyx\Connect
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/connect/index.html
 */
class Stream implements interfaces\Stream
{
    /**
     * @var array       Hash table of readable and writable stream modes for faster lookups.
     */
    private static $rwh = [
        'read' => [
            'r'   => true, 'w+'  => true, 'r+'  => true, 'rw'  => true, 'x+'  => true,
            'c+'  => true, 'a+'  => true, 'a+b' => true, 'rb'  => true, 'w+b' => true,
            'r+b' => true, 'x+b' => true, 'c+b' => true, 'rt'  => true, 'w+t' => true,
            'r+t' => true, 'x+t' => true, 'c+t' => true
        ],
        'write' => [
            'w'   => true, 'w+'  => true, 'rw'  => true, 'r+'  => true, 'c'   => true,
            'c+'  => true, 'cb'  => true, 'x'   => true, 'xb'  => true, 'x+'  => true,
            'x+b' => true, 'a'   => true, 'a+'  => true, 'a+b' => true, 'ab'  => true,
            'wb'  => true, 'w+b' => true, 'r+b' => true, 'c+b' => true, 'w+t' => true,
            'r+t' => true, 'x+t' => true, 'c+t' => true
        ]
    ];

    /**
     * @var resource    The underlying stream resource.
     */
    private $resource;

    /**
     * @var resource    The stream context resource in use.
     */
    private $context;

    /**
     * @var array       Cached data about the stream.
     */
    private $metadata;

    /**
     * @var core\Mask   Status/mode mask of the Stream.
     */
    private $status;

    /**
     * @var int         The size of the stream's contents in bytes.
     */
    private $size;

    /**
     * Constructs a new Stream instance.
     *
     * @param   string|resource     $stream     The URI of the stream resource that should be opened or an already
     *                                          created stream resource.
     * @param   string              $mode       The mode in which the stream should be opened.
     * @param   array               $context    Stream context options. Will be ignored if an already created stream
     *                                          is passed to the constructor.
     * @param   int                 $size       The size of the stream in bytes. Should only be passed if it cannot be
     *                                          obtained by directly analyzing the stream.
     * @throws  \InvalidArgumentException       When a resource was given but was not a valid stream resource or when
     *                                          it was to be created but no mode was given.
     * @throws  \RuntimeException               When a stream was to be created but couldn't be opened.
     */
    public function __construct($stream, string $mode = null, array $context = null, int $size = null)
    {
        // Are we dealing with an already existing stream?
        if (is_resource($stream)) {
            // Ensure the resource is a stream.
            if (get_resource_type($stream) !== 'stream') {
                throw new \InvalidArgumentException('A resource was given but it was not a valid stream.');
            }

            $this->resource = $stream;
        }
        // Otherwise we should create our own.
        else {
            // We need a mode to work with.
            if (null === $mode) {
                throw new \InvalidArgumentException('A valid stream mode must be given to open a stream resource.');
            }

            // Let's prepare a stream context if asked to.
            if (null !== $context) {
                $this->context = stream_context_create($context);
            }

            // Open it either with a specific context or leave the default one.
            if (!$this->resource = $this->context ? fopen($stream, $mode, $this->context) : fopen($stream, $mode)) {
                throw new \RuntimeException("Failed to open a stream [$stream, mode: $mode].");
            }
        }

        $this->size = $size;
    }

    /**
     * {@inheritDoc}
     */
    public function getContents() : string
    {
        if (!$this->resource) {
            throw new \RuntimeException('No stream resource available - cannot get contents.');
        }

        if (!$this->is(interfaces\Stream::READABLE)) {
            throw new \RuntimeException('Cannot get stream contents - the stream is not readable.');
        }

        // As long as we've got a resource, we can try reading. If it fails, we can diagnose afterwards.
        if (!false === $data = stream_get_contents($this->resource)) {
            throw new \RuntimeException('Failed to read stream contents.');
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length) : string
    {
        if (!$this->resource) {
            throw new \RuntimeException('No stream resource available - cannot read.');
        }

        if (!$this->is(interfaces\Stream::READABLE)) {
            throw new \RuntimeException('Cannot read from non-readable stream.');
        }

        if (false === $data = fread($this->resource, $length)) {
            throw new \RuntimeException('Failed to read from the stream.');
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function line(int $length = null) : string
    {
        if (!$this->resource) {
            throw new \RuntimeException('No stream resource available - cannot read.');
        }

        if (!$this->is(interfaces\Stream::READABLE)) {
            throw new \RuntimeException('Cannot read from non-readable stream.');
        }

        if (false === $data = fgets($this->resource, $length)) {
            throw new \RuntimeException('Failed to read from the stream.');
        }

        return $data;
    }


    /**
     * {@inheritdoc}
     */
    public function write($string) : int
    {
        if (!$this->resource) {
            throw new \RuntimeException('No stream resource available - cannot write.');
        }

        if (!$this->is(interfaces\Stream::WRITABLE)) {
            throw new \RuntimeException('Cannot write to non-writable stream.');
        }
        if (false === $result = fwrite($this->resource, $string)) {
            throw new \RuntimeException('Failed to write to the stream.');
        }

        // The size has changed so make sure we get a fresh value for the next getSize() call.
        $this->size = null;

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable() : bool
    {
        if (!$this->resource) {
            return false;
        }

        return $this->is(interfaces\Stream::READABLE);
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable() : bool
    {
        if (!$this->resource) {
            return false;
        }

        return $this->is(interfaces\Stream::WRITABLE);
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable() : bool
    {
        if (!$this->resource) {
            return false;
        }

        return $this->is(interfaces\Stream::SEEKABLE);
    }

    /**
     * {@inheritDoc}
     */
    public function is($status) : bool
    {
        if (!$this->resource) {
            return false;
        }

        if (!$this->metadata || $this->status) {
            $this->refresh();
        }

        return $this->status->is($status);
    }

    /**
     * {@inheritdoc}
     */
    public function tell()
    {
        if (!$this->resource) {
            throw new \RuntimeException('No stream resource available - cannot tell position.');
        }

        if (false === $result = ftell($this->resource)) {
            throw new \RuntimeException('Unable to determine stream position.');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        return !$this->resource || feof($this->resource);
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!$this->resource) {
            throw new \RuntimeException('No stream resource available - cannot seek.');
        }

        if (!$this->is(interfaces\Stream::SEEKABLE)) {
            throw new \RuntimeException('The stream is not seekable.');
        }

        // As long as we've got a resource, we can try seeking. If it fails, we can diagnose afterwards.
        if (-1 === fseek($this->resource, $offset, $whence)) {
            throw new \RuntimeException("Failed to seek to stream position [$offset] with whence [".var_export($whence, true)."].");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        if (!$this->resource) {
            return null;
        }

        if ($this->size !== null) {
            return $this->size;
        }

        // We're gonna clear the cache for local streams with an URI to ensure we get
        // an updated size each time, in case we or something else is currently writing
        // to that resource.
        if ($this->is(interfaces\Stream::LOCAL) && $uri = $this->getMetadata('uri')) {
            clearstatcache(true, $uri);
        }

        $stats = fstat($this->resource);

        if (isset($stats['size'])) {
            return $this->size = $stats['size'];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        // If we've got no underlying stream (anymore?), we are going to return null for each requested
        // key and empty arrays if the whole metadata array was requested.
        if (!$this->resource) {
            return $key ? null : [];
        }

        // Certain data doesn't change in a given stream, so we might just as well return cached values
        // if we've got them.
        if ($key && $this->metadata) {
            switch ($key) {
                case 'mode':
                case 'stream_type':
                case 'wrapper_type':
                case 'wrapper_data':
                case 'uri':
                    return $this->metadata[$key];
            }
        }

        // Grab the metadata and cache it.
        $this->metadata = stream_get_meta_data($this->resource);

        if (null === $key) {
            return $this->metadata;
        }

        if (!array_key_exists($key, $this->metadata)) {
            return null;
        }

        return $this->metadata[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if (!$this->resource || !is_resource($this->resource)) {
            return;
        }

        // Close first, detach afterwards.
        fclose($this->resource);

        $this->detach();
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        $resource = $this->resource;

        $this->resource = null;
        $this->context  = null;
        $this->metadata = null;
        $this->status   = null;
        $this->size     = null;

        return $resource;
    }

    /**
     * {@inheritDoc}
     */
    public function toString() : string
    {
        // Trying to get the position first - we'll seek back to it after we're done.
        // This will also take care of all necessary checks.
        $position = $this->tell();

        // Grab the contents into memory. This might potentially use up a lot of memory
        // so be advised about the pitfalls.
        $body = stream_get_contents($this->resource, -1, 0);

        // Seek back to where we were.
        $this->seek($position);

        return (string) $body;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        try {
            return $this->toString();
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Ensure the stream resource gets closed when the Stream instance gets destructed.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Refreshes the status mask based on the current metadata of the stream.
     *
     * @return  bool    True if we successfully refreshed all relevant data, false otherwise.
     */
    protected function refresh() : bool
    {
        // Without a resource to grab the metadata for, let invokers know there's no data
        // to work with presently.
        if (!$this->resource) {
            return false;
        }

        // Prepare the status mask if necessary. Might as well give it a value to begin with.
        if (!$this->status) {
            $this->status = new core\Mask(stream_is_local($this->resource) ? interfaces\Stream::LOCAL : 0);
        }

        // The call results of metadata() are cached so we can just use the class property.
        $this->getMetadata();

        if (isset(static::$rwh['read'][$this->metadata['mode']])) {
            $this->status->set(interfaces\Stream::READABLE);
        }

        if (isset(static::$rwh['write'][$this->metadata['mode']])) {
            $this->status->set(interfaces\Stream::WRITABLE);
        }

        // Those may change, so... besides - fancy syntax, eh chaps?
        $this->status->{((isset($this->metadata['seekable']) && $this->metadata['seekable']) ? 'set' : 'remove')}(interfaces\Stream::SEEKABLE);
        $this->status->{((isset($this->metadata['blocked'])  && $this->metadata['blocked'])  ? 'set' : 'remove')}(interfaces\Stream::BLOCKED);

        return true;
    }
}
