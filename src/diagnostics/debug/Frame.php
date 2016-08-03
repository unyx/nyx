<?php namespace nyx\diagnostics\debug;

// External dependencies
use nyx\core;
use nyx\utils;

/**
 * Frame
 *
 * Represents a single frame from a diagnostic inspection, ie. from a stack trace.
 *
 * @package     Nyx\Diagnostics\Debug
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/diagnostics/debug.html
 * @todo        setData() validation.
 */
class Frame implements core\interfaces\Serializable
{
    /**
     * The traits of a Frame instance.
     */
    use core\traits\Serializable;
    use utils\traits\Jetget;

    /**
     * @var int     The line in which the error occurred.
     */
    private $line;

    /**
     * @var string  The namespace the error occurred in, if any.
     */
    private $namespace;

    /**
     * @var string  The fully-qualified name (ie. with namespace) of the class the error occurred in, if any.
     */
    private $class;

    /**
     * @var string  The short name (ie. without namespace) of the class the error occurred in, if any.
     */
    private $shortClass;

    /**
     * @var string  The name of the function/method the error occurred in.
     */
    private $function;

    /**
     * @var string  The type of the error.
     */
    private $type;

    /**
     * @var array   The context the error occurred in.
     */
    private $args;

    /**
     * @var string  The path to the file the error occurred in.
     */
    private $file;

    /**
     * @var int     The nesting limit of flattened args.
     */
    private $nestingLimit = 10;

    /**
     * @var array   An array of cached file paths => file contents. Kept static to reduce IO overhead when
     *              multiple Frames are assigned to the same files.
     */
    private static $files;

    /**
     * Constructs the Frame.
     *
     * @param   array   $data   The frame's data, in the format returned by \Exception::getTrace().
     */
    public function __construct(array $data)
    {
        $this->setData($data);
    }

    /**
     * Returns the number of the line which got executed resulting in the inspected trace.
     *
     * @return  int The number of the line, or 0 if the line is unknown.
     */
    public function getLine() : int
    {
        return $this->line;
    }

    /**
     * Returns the fully qualified name of the class which contained the code that resulted in the inspected trace.
     *
     * @return  string  The fully qualified name of the class, or an empty string if the class is unknown or the
     *                  code was not contained in a class.
     */
    public function getClass() : string
    {
        return $this->class;
    }

    /**
     * Returns the namespace of the class which contained the code that resulted in the inspected trace.
     *
     * @return  string  The namespace, or an empty string if: the namespace is unknown; the class is unknown
     *                  or was contained in the global namespace; the code was not contained in a class.
     */
    public function getNamespace() : string
    {
        return $this->namespace;
    }

    /**
     * Returns the shortened name (without the namespace) of the class which contained the code that
     * resulted in the inspected trace.
     *
     * @return  string  The shortened name of the class, or an empty string if the class is unknown or the
     *                  code was not contained in a class.
     */
    public function getShortClass() : string
    {
        return $this->shortClass;
    }

    /**
     * Returns the name of the function/method which got executed resulting in the inspected trace.
     *
     * @return  string  The name of the function/method, or an empty string if the name is unknown or the
     *                  code was not executed inside a function/method.
     */
    public function getFunction() : string
    {
        return $this->function;
    }

    /**
     * Returns the type of the function/method call (static or dynamic).
     *
     * @return  string  The type of the call, or an empty string if the type is unknown.
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * Returns the arguments which were passed to the function resulting in the inspected trace.
     *
     * @return  array   The arguments (context) of the call, or an empty array if there were none or
     *                  they are unknown.
     */
    public function getArgs() : array
    {
        return $this->args;
    }

    /**
     * Returns the path to the file assigned to the current frame.
     *
     * @return  string  The path, or an empty string if the path is unknown.
     */
    public function getFile() : string
    {
        return $this->file;
    }

    /**
     * Returns the contents of the file assigned to this frame as a string.
     *
     * @return  string  Returns either the contents of the assigned file or an empty string if no file is assigned
     *                  or its contents could not be fetched.
     */
    public function getFileContents() : string
    {
        // No point in continuing if there is no file assigned to this frame.
        if (!$this->file || $this->file === 'Unknown') {
            return '';
        }

        // If the contents aren't cached yet, grab them into our rudimentary in-memory cache.
        // Note: This may cache a boolean false if the retrieval fails for whatever reason. We're gonna handle
        // that afterwards and return an empty string instead.
        if (!isset(static::$files[$this->file])) {
            static::$files[$this->file] = file_get_contents($this->file);
        }

        // Return the cached contents of the file or an empty string if no contents could be fetched.
        return static::$files[$this->file] ?: '';
    }

    /**
     * Returns the contents of the file assigned to this frame as an array of lines optionally sliced from
     * the given starting line to the given ending line. The arguments used work in exactly the same way as
     * {@see array_splice()}.
     *
     * Note: Lines are 0-indexed.
     *
     * @param   int $offset     The starting offset.
     * @param   int $length     The length of the resulting subset.
     * @return  string[]|null   The resulting array of lines or an empty array when no file contents are available.
     */
    public function getFileLines(int $offset = 0, int $length = null) : array
    {
        // Return null right away if we are not able to grab the file contents for any reason.
        if (!$contents = $this->getFileContents()) {
            return [];
        }

        // Explode the contents into an array by line breaks and return the slice. Note: Normally we'd simply read
        // the contents into an array directly by using file(), but to avoid code duplication and to keep caching
        // simple it's done as-is.
        return array_slice(explode("\n", $contents), $offset, $length, true);
    }

    /**
     * {@inheritDoc}
     */
    public function serialize() : string
    {
        $data = $this->toArray();

        if (!empty($data['args'])) {
            $data['args'] = $this->flattenArgs($data['args']);
        }

        return serialize($data);
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($data)
    {
        $this->setData(unserialize($data));
    }

    /**
     * {@inheritDoc}
     */
    public function toArray() : array
    {
        return [
            'file'     => $this->file,
            'line'     => $this->line,
            'function' => $this->function,
            'class'    => $this->class,
            'type'     => $this->type,
            'args'     => $this->args
        ];
    }

    /**
     * Sets the Frame's data.
     *
     * @param   array   $data   The Frame's data, in the same format as returned by \Exception::getTrace().
     * @return  $this
     */
    protected function setData(array $data) : Frame
    {
        $this->file     = $data['file']     ?? '';
        $this->line     = $data['line']     ?? 0;
        $this->class    = $data['class']    ?? '';
        $this->function = $data['function'] ?? '';
        $this->type     = $data['type']     ?? '';
        $this->args     = $data['args']     ?? [];

        // If we're dealing with a class, try to get its namespace and short name.
        if (!empty($this->class)) {
            $parts = explode('\\', $this->class);

            $this->shortClass = array_pop($parts);
            $this->namespace  = implode('\\', $parts);
        } else {
            // Otherwise initialize with zero-values.
            $this->shortClass = '';
            $this->namespace  = '';
        }

        return $this;
    }

    /**
     * Flattens the args to make them easier to serialize.
     *
     * @param   array   $args   The args to flatten.
     * @param   int     $depth  The current nesting depth.
     * @return  array           The flattened args.
     */
    protected function flattenArgs(array $args, int $depth = 0) : array
    {
        $result = [];

        foreach ($args as $key => $value) {
            if (is_object($value)) {
                $result[$key] = ['object', get_class($value)];
            } elseif (is_array($value)) {
                if ($depth > $this->nestingLimit) {
                    $result[$key] = ['array', '*DEEP NESTED ARRAY*'];
                } else {
                    $result[$key] = ['array', $this->flattenArgs($value, ++$depth)];
                }
            } elseif (null === $value) {
                $result[$key] = ['null', null];
            } elseif (is_bool($value)) {
                $result[$key] = ['boolean', $value];
            } elseif (is_resource($value)) {
                $result[$key] = ['resource', get_resource_type($value)];
            } elseif ($value instanceof \__PHP_Incomplete_Class) {
                // Special case of object - is_object() will return false.
                $array = new \ArrayObject($value);
                $result[$key] = ['incomplete-object', $array['__PHP_Incomplete_Class_Name']];
            } else {
                $result[$key] = ['string', (string) $value];
            }
        }

        return $result;
    }
}
