<?php namespace nyx\core\collections\exceptions;

// Internal dependencies
use nyx\core\collections;

/**
 * Key Does Not Exist In Collection Exception
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class KeyNotExists extends collections\Exception
{
    /**
     * @var mixed   The key that was not found.
     */
    private $key;

    /**
     * {@inheritdoc}
     *
     * @param   mixed   $key    The key that was not found.
     */
    public function __construct(collections\interfaces\Collection $collection, $key, string $message = null, $code = 0, $previous = null)
    {
        $this->key = $key;

        parent::__construct($collection, $message ?? "No item with this key [$key] exists in the Collection.", $code, $previous);
    }

    /**
     * Returns the key that was not found.
     *
     * @return  mixed
     */
    public function getKey()
    {
        return $this->key;
    }
}
