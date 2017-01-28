<?php namespace nyx\core\traits;

// Internal dependencies
use nyx\core\interfaces;

/**
 * Named
 *
 * A Named object is one that has a name which can be get and set, and needs to conform to certain rules.
 *
 * This trait allows for the implementation of the core\interfaces\Named interface.
 *
 * The default assumption is that a Named object is one whose name is required to be not empty. If that is not the
 * case and the name does not need to meet any rules that could be defined in an overridden validateName() method,
 * the usage worth of this trait becomes highly questionable.
 *
 * @package     Nyx\Core
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
trait Named
{
    /**
     * @var string  The name of this object.
     */
    private $name;

    /**
     * @see \nyx\core\interfaces\Named::getName()
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @see \nyx\core\interfaces\Named::setName()
     */
    public function setName(string $name) : interfaces\Named
    {
        $this->name = $this->assertValidName($name);

        return $this;
    }

    /**
     * Assets the given string is valid to be used as a name and returns it if it is.
     *
     * @param   string                      $name   The name to be validated.
     * @return  string
     * @throws  \InvalidArgumentException           When the name does not conform to the validation rules.
     */
    protected function assertValidName(string $name) : string
    {
        if (empty($name)) {
            throw new \InvalidArgumentException("A name must be a non-empty string.");
        }

        return $name;
    }
}
