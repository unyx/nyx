<?php namespace nyx\console\output\formatting\styles;

// External dependencies
use nyx\core\collections;

// Internal dependencies
use nyx\console\output\formatting\interfaces;

/**
 * Styles Map
 *
 * Items in the collection are identified by names, although those names are not set explicitly within the Style
 * instances themselves.
 *
 * While those names can be used by Output Formatters to determine how to style text within the respective tags,
 * neither the collection nor the instances run under the assumption of being used only in that context.
 * As such, naming them is merely a helpful means of accessing them.
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Map extends collections\Collection
{
    /**
     * The traits of a Styles Map.
     */
    use collections\traits\Collection;

    /**
     * Returns a Style identified by its name.
     *
     * @param   string  $name       The name of the Style to return.
     * @param   mixed   $default    The default value to return when the given Style does not exist in the Collection.
     * @return  interfaces\Style    The Style or the default value given if the Style couldn't be found.
     */
    public function get(string $name, $default = null) : ?interfaces\Style
    {
        return $this->items[strtolower($name)] ?? $default;
    }

    /**
     * Sets the given Style in the Collection.
     *
     * @param   string  $name   The name the Style should be set as.
     * @param   mixed   $value  The Style to set.
     * @return  $this
     */
    public function set(string $name, interfaces\Style $style) : Map
    {
        $this->items[strtolower($name)] = $style;

        return $this;
    }

    /**
     * Checks whether the given Style identified by its name exists in the Collection.
     *
     * @param   string  $name   The name of the Style to check for.
     * @return  bool            True if the Style exists in the Collection, false otherwise.
     */
    public function has(string $name) : bool
    {
        return isset($this->items[strtolower($name)]);
    }

    /**
     * Checks whether the given Style exists in the Collection.
     *
     * @param   interfaces\Style    $style  The Style to search for.
     * @return  bool                        True if the Style exists in the Collection, false otherwise.
     */
    public function contains(interfaces\Style $style) : bool
    {
        return empty($this->items) ? false : (null !== $this->name($style));
    }

    /**
     * Removes a Style from the Collection by its name.
     *
     * @param   string  $name   The name of the Style to remove.
     * @return  $this
     */
    public function remove(string $name) : Map
    {
        unset($this->items[strtolower($name)]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function replace($items) : collections\interfaces\Collection
    {
        $this->items = [];

        foreach ($this->extractItems($items) as $name => $style) {
            $this->set($name, $style);
        }

        return $this;
    }

    /**
     * Returns the name a given Style is set as within the Collection.
     *
     * @param   interfaces\Style    $style  The Style to search for.
     * @return  string                      The name of the Style or null if it couldn't be found.
     */
    public function name(interfaces\Style $style) : ?string
    {
        foreach ($this->items as $key => $value) {
            if ($value === $style) {
                return $key;
            }
        }

        return null;
    }

    /**
     * @see Map::get()
     */
    public function __get(string $name) : ?interfaces\Style
    {
        return $this->get($name);
    }

    /**
     * @see Map::set()
     */
    public function __set(string $name, interfaces\Style $style) : Map
    {
        return $this->set($name, $style);
    }

    /**
     * @see Map::has()
     */
    public function __isset(string $name) : bool
    {
        return $this->has($name);
    }

    /**
     * @see Map::remove()
     */
    public function __unset(string $name) : Map
    {
        return $this->remove($name);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($name) : interfaces\Style
    {
        return $this->get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($name, $item) : Map
    {
        return $this->set($name, $item);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($name) : bool
    {
        return $this->has($name);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($name) : Map
    {
        return $this->remove($name);
    }
}
