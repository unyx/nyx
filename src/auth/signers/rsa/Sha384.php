<?php namespace nyx\auth\signers\rsa;

// Internal dependencies
use nyx\auth\signers;

/**
 * RSA-SHA384 Signer
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Sha384 extends signers\Rsa
{
    /**
     * {@inheritDoc}
     */
    const ALGORITHM = OPENSSL_ALGO_SHA384;
}
