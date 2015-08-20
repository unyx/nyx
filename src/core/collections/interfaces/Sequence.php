<?php namespace nyx\core\collections\interfaces;

/**
 * Sequence Interface
 *
 * Items in a Sequence are ordered numerically and the order gets updated whenever an item
 * is moved out or added to the Sequence. The same values may occur more than once in a Sequence.
 *
 * @package     Nyx\Core\Collections
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/core/collections.html
 */
interface Sequence extends Collection
{
    /**
     * Returns the item set at the given index.
     *
     * @param   string|int  $key        The index of the item to return.
     * @param   mixed       $default    The default value to return when the given item does not exist in
     *                                  the Collection.
     * @return  mixed                   The item or the default value given if the item couldn't be found.
     */
    public function get(int $index, $default = null);

    /**
     * Pushes the given item to the end of the Sequence.
     *
     * @param   mixed   $item   The item to set.
     * @return  $this
     */
    public function push($item) : self;

    /**
     * Prepends the given item to the beginning of the Sequence.
     *
     * @param   mixed   $item   The item to set.
     * @return  $this
     */
    public function prepend($item) : self;

    /**
     * Updates the item at the given index.
     *
     * Note: This differs from Map::set() in that an item must already exist at the given index,
     * otherwise an Exception will be thrown. Use self::add() to add an item.
     *
     * @param   int     $index              The index of the item to update.
     * @param   mixed   $value              The value of the item to set.
     * @return  $this
     * @throws  \OutOfBoundsException       When there is no item to update at the given index.
     */
    public function update(int $index, $item) : self;

    /**
     * Checks whether the given item identified by its key exists in the Sequence.
     *
     * @param   int  $index     The index of the item to check for.
     * @return  bool            True if the item exists in the Sequence, false otherwise.
     */
    public function has(int $index) : bool;

    /**
     * Checks whether the given item identified by its value exists in the Sequence.
     *
     * @param   mixed   $item       The value of the item to check for.
     * @return  bool                True if the item exists in the Sequence, false otherwise.
     */
    public function contains($item) : bool;

    /**
     * Removes an item from the Sequence by its index.
     *
     * @param   int     $key                The index of the item to remove.
     * @return  $this
     */
    public function remove(int $index) : self;

    /**
     * Returns and then removes the first item from the Sequence.
     *
     * @return  mixed|null
     */
    public function shift();

    /**
     * Returns and then removes the last item from the Sequence.
     *
     * @return  mixed|null
     */
    public function pop();

    /**
     * Returns the first found index of the given item.
     *
     * @param   mixed   $item   The value of the item to check for.
     * @return  int             The 0-based index of the item if found, -1 otherwise.
     */
    public function indexOf($item) : int;

    /**
     * Returns the last found index of the given item.
     *
     * @param   mixed   $item   The value of the item to check for.
     * @return  int             The 0-based index of the item if found, -1 otherwise.
     */
    public function lastIndexOf($item) : int;

    /**
     * Returns the indices of the items in this Sequence. Acts similar to array_keys() except
     * for strict comparisons being enforced.
     *
     * @param   mixed   $of         If given, only the indices of the given items (values) will be returned.
     * @return  array
     */
    public function indices($of = null) : array;
}
