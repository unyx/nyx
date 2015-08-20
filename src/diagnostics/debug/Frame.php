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
     * Returns the Frame's data as an array.
     *
     * @return  array   $data   The Frame's data, in the same format as returned by \Exception::getTrace().
     */
    public function getData() : array
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
     */
    public function setData(array $data)
    {
        $this->line     = isset($data['line']) ? $data['line'] : null;
        $this->class    = isset($data['class']) ? $data['class'] : null;
        $this->function = isset($data['function']) ? $data['function'] : null;
        $this->type     = isset($data['type']) ? $data['type'] : null;
        $this->args     = isset($data['args']) ? (array) $data['args'] : [];
        $this->file     = isset($data['file']) ? $data['file'] : null;

        // If we're dealing with a class, try to get its namespace and short name.
        if (null !== $this->class) {
            $parts = explode('\\', $this->class);

            $this->shortClass = array_pop($parts);
            $this->namespace  = implode('\\', $parts);
        }
    }

    /**
     * Returns the line which got executed resulting in the inspected trace.
     *
     * @return  int|null
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * Returns the name of the class which got executed resulting in the inspected trace.
     *
     * @return  string|null
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Returns the namespace of the class which got executed resulting in the inspected trace.
     *
     * @return  string|null
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Returns the shortened name (without the namespace) of the class which got executed resulting in the
     * inspected trace.
     *
     * @return  string|null
     */
    public function getShortClass()
    {
        return $this->shortClass;
    }

    /**
     * Returns the function/method which got executed resulting in the inspected trace.
     *
     * @return  string|null
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * Returns the type of the function/method call (static or dynamic).
     *
     * @return  string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns the arguments which were passed to the function resulting in the inspected trace.
     *
     * @return  array
     */
    public function getArgs() : array
    {
        return $this->args;
    }

    /**
     * Returns the path to the file assigned to the current frame.
     *
     * @return  string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Returns the contents of the file assigned to this frame as a string.
     *
     * @return  string|bool     Returns either the contents of the assigned file or false if no file is assigned
     *                          or its contents could not be fetched.
     */
    public function getFileContents()
    {
        // No point in continuing if there is no file assigned to this frame.
        if (!$this->file || $this->file === 'Unknown') {
            return false;
        }

        // If the assigned file's contents are already cached, return them right away.
        // Note: "False" might also be cached instead of the actual file contents if retrieval
        // of the contents failed once already.
        if (isset(static::$files[$this->file])) {
            return static::$files[$this->file];
        }

        // Otherwise grab the contents, cache them and return them.
        return static::$files[$this->file] = file_get_contents($this->file);
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
     * Returns the file path (if present) with the part common for all frames replaced by an ellipsis and slashes
     * replaced by soft slashes for presentation purposes (word wrapping).
     *
     * @return  string
     * @todo    Replace the dirname() calls with a required string parameter for the path which shall get cut out.
     */
    public function getPrettyPath()
    {
        if ($path = $this->file) {
            $dirname = dirname(dirname(dirname(dirname(dirname(dirname(__DIR__))))));
            $path = str_replace($dirname, "…", $path);
        }

        return $path;
    }

    /**
     * {@inheritDoc}
     */
    public function serialize()
    {
        $data = $this->toArray();

        !empty($data['args']) && $data['args'] = $this->flattenArgs($data['args']);

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
        return $this->getData();
    }

    /**
     * Flattens the args to make them easier to serialize.
     *
     * @param   array   $args   The args to flatten.
     * @param   int     $level  The current nesting depth.
     * @return  array           The flattened args.
     */
    protected function flattenArgs(array $args, $level = 0)
    {
        $result = [];

        foreach ($args as $key => $value) {
            if (is_object($value)) {
                $result[$key] = ['object', get_class($value)];
            } elseif (is_array($value)) {
                if ($level > $this->nestingLimit) {
                    $result[$key] = ['array', '*DEEP NESTED ARRAY*'];
                } else {
                    $result[$key] = ['array', $this->flattenArgs($value, ++$level)];
                }
            } elseif (null === $value) {
                $result[$key] = ['null', null];
            } elseif (is_bool($value)) {
                $result[$key] = ['boolean', $value];
            } elseif (is_resource($value)) {
                $result[$key] = ['resource', get_resource_type($value)];
            }
            // Special case of object, is_object will return false.
            elseif ($value instanceof \__PHP_Incomplete_Class) {
                $array = new \ArrayObject($value);
                $result[$key] = ['incomplete-object', $array['__PHP_Incomplete_Class_Name']];
            } else {
                $result[$key] = ['string', (string) $value];
            }
        }

        return $result;
    }
}
