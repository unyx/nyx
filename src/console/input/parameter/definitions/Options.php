<?php namespace nyx\console\input\parameter\definitions;

// External dependencies
use nyx\core;

// Internal dependencies
use nyx\console\input;

/**
 * Input Option Definitions
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Options extends input\parameter\Definitions
{
    /**
     * @var array   A map of Option shortcuts to their actual names.
     */
    private $shortcuts = [];

    /**
     * {@inheritdoc}
     */
    public function __construct($options = null)
    {
        $this->setCollectedType(input\Option::class);

        parent::__construct($options);
    }

    /**
     * {@inheritdoc}
     *
     * Overridden to allow defining Option instances without explicitly instantiating them.
     */
    public function replace($items) : core\collections\interfaces\Collection
    {
        $this->items     = [];
        $this->shortcuts = [];

        foreach ($this->extractItems($items) as $item) {
            $this->add(is_array($item) ? $this->unpack($item) : $item);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function add(core\interfaces\Named $option) : core\collections\interfaces\NamedObjectSet
    {
        parent::add($option);

        // If the Option has a shortcut...
        /* @var input\Option $option */
        if ($shortcut = $option->getShortcut()) {
            $this->shortcuts[$shortcut] = $option->getName();
        }

        return $this;
    }

    /**
     * Returns the map of Option shortcuts to their actual names.
     *
     * @return  array
     */
    public function getShortcuts() : array
    {
        return $this->shortcuts;
    }

    /**
     * Returns the Option matching a given shortcut name.
     *
     * @param   string          $shortcut   The name of the shortcut.
     * @return  input\Option
     * @throws  \OutOfBoundsException       When the given shortcut is not defined.
     */
    public function getByShortcut(string $shortcut) : input\Option
    {
        if (!isset($this->shortcuts[$shortcut])) {
            throw new \OutOfBoundsException("The short option [$shortcut] is not defined.");
        }

        return $this->get($this->shortcuts[$shortcut]);
    }

    /**
     * Unpacks a sequence of Option constructor arguments into an Option instance.
     *
     * @see     \nyx\console\input\Option::__construct()
     *
     * @param   array           $definition     The arguments to unpack. The order must match the constructor's signature.
     * @return  input\Option
     */
    protected function unpack(array $definition) : input\Option
    {
        // If the 4th argument is an integer, we are going to assume it's one of the input\Value
        // class constants defining the mode and attempt to instantiate a input\Value with such.
        if (isset($definition[3]) && is_int($definition[3])) {
            $definition[3] = new input\Value($definition[3]);
        }

        return new input\Option(...$definition);
    }
}
