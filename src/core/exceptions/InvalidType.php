<?php namespace nyx\core\exceptions;

// External dependencies
use nyx\diagnostics;

/**
 * Invalid Type Exception
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class InvalidType extends \LogicException
{
    /**
     * Constructs a new Invalid Type Exception.
     *
     * @param   mixed           $value          The invalid value.
     * @param   array|string    $expectation    A list of the expected type(s) or a description of them.
     */
    public function __construct($value, $expectation = null, $code = 0, $previous = null)
    {
        if (!empty($expectation)) {
            if (is_array($expectation)) {
                $expectation = 'Expected '.(count($expectation) === 1 ? 'a' : 'one of').' ['.implode('|', $expectation).']';
            }

            $message = $expectation.', got ['.diagnostics\Debug::getTypeName($value).'] instead.';
        } else {
            $message = '['.diagnostics\Debug::getTypeName($value).'] is a unsupported type.';
        }

        parent::__construct($message, $code, $previous);
    }
}
