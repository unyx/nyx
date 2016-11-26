<?php namespace nyx\auth;

/**
 * Signer
 *
 * Security note on Signers: The hashers in this component are *absolutely not* meant for hashing passwords.
 * They are used for generating signatures of messages, which in turn are used for verifying the authenticity
 * of those messages, but are not intended to be used outside of that purpose.
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
abstract class Signer implements interfaces\Signer
{
    /**
     * The name/identifier of the hashing method this Signer uses.
     */
    const METHOD = null;

    /**
     * The name/identifier of the hashing algorithm this Signer uses.
     */
    const ALGORITHM = null;

    /**
     * {@inheritDoc}
     */
    public function getMethod() : string
    {
        return static::METHOD;
    }

    /**
     * {@inheritDoc}
     */
    public function getAlgorithm() : string
    {
        return static::ALGORITHM;
    }
}
