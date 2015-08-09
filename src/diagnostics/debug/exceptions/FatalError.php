<?php namespace nyx\diagnostics\debug\exceptions;

/**
 * Fatal Error Exception
 *
 * Extends the special Error Exception {@see Error} to make a distinction between casual errors converted to
 * Exceptions and errors considered fatal. Those fatal errors get caught by debug\handlers\Error::onShutdown when
 * it is registered as a shutdown function and then get converted to instances of this class.
 *
 * @package     Nyx\Diagnostics\Debug
 * @version     0.0.5
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/diagnostics/debug.html
 */
class FatalError extends Error
{

}
