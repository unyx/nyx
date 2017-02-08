<?php namespace nyx\core\collections;

/**
 * Collection Exception
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Exception extends \RuntimeException
{
    /**
     * @var interfaces\Collection   The Collection in which this Exception occurred.
     */
    private $collection;

    /**
     * {@inheritdoc}
     *
     * @param   interfaces\Collection   $collection     The Collection in which this Exception occurred.
     */
    public function __construct(interfaces\Collection $collection, string $message, $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns the Collection in which this Exception occurred.
     *
     * @return  interfaces\Collection
     */
    public function getCollection() : interfaces\Collection
    {
        return $this->collection;
    }
}
