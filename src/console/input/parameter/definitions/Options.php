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
     */
    public function replace($items) : core\collections\interfaces\Collection
    {
        $this->items     = [];
        $this->shortcuts = [];

        foreach ($this->extractItems($items) as $item) {

            // Allow for defining Option instances without explicitly instantiating them
            // when creating Input Definitions.
            if (is_array($item)) {
                $item = new input\Option(...$item);
            }

            $this->add($item);
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
}
