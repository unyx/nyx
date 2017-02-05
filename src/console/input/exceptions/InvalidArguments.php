<?php namespace nyx\console\input\exceptions;

// Internal dependencies
use nyx\console\input\parameter\values\Arguments;

/**
 * Invalid Arguments Exception
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class InvalidArguments extends InvalidParameters
{
    /**
     * @var Arguments    The Input Arguments which caused the Exception.
     */
    private $arguments;

    /**
     * {@inheritDoc}
     *
     * @param   Arguments    $arguments  The Input Arguments which caused the Exception.
     */
    public function __construct(Arguments $arguments, string $message = null, int $code = 0, \Exception $previous = null)
    {
        $this->arguments = $arguments;

        // Proceed to create the base Exception.
        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns the Input Arguments which caused the Exception.
     *
     * @return  Arguments
     */
    public function getArguments() : Arguments
    {
        return $this->arguments;
    }
}
