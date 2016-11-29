<?php namespace nyx\console;

/**
 * Terminal
 *
 * @package     Nyx\Console
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
abstract class Terminal implements interfaces\Terminal
{
    /**
     * @var array   The Terminal's dimensions.
     */
    protected $dimensions;

    /**
     * Attempts to determine the Terminal's dimensions.
     *
     * @return  array   An array containing two keys - 'width' and 'height'. The values for those keys can be null
     *                  if the respective value could not be determined.
     */
    abstract protected function getDimensions() : ?array;

    /**
     * Creates a new Terminal instance.
     */
    public function __construct()
    {
        $this->flushDimensions();
    }

    /**
     * {@inheritDoc}
     */
    public function getWidth(int $default = 80) : int
    {
        if (isset($this->dimensions['width'])) {
            return $this->dimensions['width'];
        }

        // Environmental variables take priority and should be platform-agnostic.
        if ($width = trim(getenv('COLUMNS'))) {
            return $this->dimensions['width'] = (int) $width;
        }

        return $this->getDimensions()['width'] ?? $default;
    }

    /**
     * {@inheritDoc}
     */
    public function getHeight(int $default = 32) : int
    {
        if (isset($this->dimensions['height'])) {
            return $this->dimensions['height'];
        }

        // Environmental variables take priority and should be platform-agnostic.
        if ($height = trim(getenv('LINES'))) {
            return $this->dimensions['height'] = (int) $height;
        }

        return $this->getDimensions()['height'] ?? $default;
    }

    /**
     * Resets the cached dimensions of the underlying terminal.
     *
     * In usual execution flows the dimensions are unlikely to change. However, when running REPLs or long processes,
     * there is a likelihood of the Terminal's window dimensions to change. For those it makes sense to manually
     * flush the values before requesting them, when they are to be relied upon.
     *
     * @return  $this
     */
    public function flushDimensions() : Terminal
    {
        $this->dimensions = [
            'width'  => null,
            'height' => null
        ];

        return $this;
    }

    /**
     * Executes a system call and returns its output, while suppressing any error output.
     *
     * Requires proc_open() to be available on the platform.
     *
     * @param   string  $command    The command to execute.
     * @return  string              The output of the process or null if the process could not be executed.
     * @todo                        Use the Process component instead since we're relying on proc_open() anyways?
     */
    protected function execute(string $command) : ?string
    {
        // We require proc_open() to suppress error output.
        if (!function_exists('proc_open')) {
            return null;
        }

        // Define the file pointers we are going to utilize.
        $descriptors = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];

        // Execute the command with error suppression. Make sure we got a valid resource to work with.
        if (!is_resource($process = proc_open($command, $descriptors, $pipes, null, null, ['suppress_errors' => true]))) {
            return null;
        }

        $output = stream_get_contents($pipes[1]);

        // Close all open resource pointers.
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        return $output;
    }
}
