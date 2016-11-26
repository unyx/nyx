<?php namespace nyx\auth\signers\hmac;

// Internal dependencies
use nyx\auth\signers;

/**
 * HMAC-SHA256 Signer
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Sha256 extends signers\Hmac
{
    /**
     * {@inheritDoc}
     */
    const ALGORITHM = 'sha256';
}
