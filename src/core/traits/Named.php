<?php namespace nyx\core\traits;

/**
 * Named
 *
 * This trait allows for the implementation of the core\interfaces\Named interface.
 *
 *  A Named object is one that has a name which needs to conform to certain rules.
 *
 * The default assumption is that a Named object is one whose name is required to be not empty. If that is not the
 * case and the name does not need to meet any rules that could be defined in an overridden validateName() method,
 * the usage worth of this trait becomes highly questionable.
 *
 * @package     Nyx\Core
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/core/index.html
 */
trait Named
{
    /**
     * @var string  The name of this object.
     */
    private $name;

    /**
     * @see core\interfaces\Named::getName()
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @see core\interfaces\Named::setName()
     */
    public function setName(string $name)
    {
        $this->validateName($name);
        $this->name = $name;

        return $this;
    }

    /**
     * Validates the given name to ensure that it is valid. Default implementation only checks whether
     * the name is not empty. More specific rules are left to the implementer.
     *
     * @param   string                      $name   The name to be validated.
     * @throws  \InvalidArgumentException           When the name does not conform to the validation rules.
     */
    protected function validateName(string $name)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException("A name must be a non-empty string.");
        }
    }
}
