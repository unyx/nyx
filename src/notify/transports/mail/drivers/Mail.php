<?php namespace nyx\notify\transports\mail\drivers;

// Internal dependencies
use nyx\notify\transports\mail\interfaces;

/**
 * Mail() Driver
 *
 * Utilizes PHP's native mail() function. Should in general be avoided in favour of more robust transports, unless
 * those are not available.
 *
 * Do note that mail deliverability and sender reputation are important and multi-faceted concerns
 * the native mail() function does not cover.
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Mail extends \Swift_MailTransport implements interfaces\Driver
{

}
