<?php namespace nyx\core\collections\exceptions;

// Internal dependencies
use nyx\core\collections;

/**
 * Key Already Exists In Collection Exception
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class KeyAlreadyExists extends collections\Exception
{
    /**
     * @var mixed   The key the value was attempted to be set as.
     */
    private $key;

    /**
     * @var mixed   The value that was attempted to be set with the given key.
     */
    private $value;

    /**
     * {@inheritdoc}
     *
     * @param   mixed   $key    The key the value was attempted to be set as.
     * @param   mixed   $value  The value that was attempted to be set with the given key.
     */
    public function __construct(collections\interfaces\Collection $collection, $key, $value, string $message = null, $code = 0, $previous = null)
    {
        $this->key   = $key;
        $this->value = $value;

        parent::__construct($collection, $message ?? "An item with this key [$key] has already been set.", $code, $previous);
    }

    /**
     * Returns the key the value was attempted to be set as.
     *
     * @return  mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Returns the value that was attempted to be set with the given key.
     *
     * @return  mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
