<?php namespace nyx\core\collections\interfaces;

// Internal dependencies
use nyx\core;

/**
 * Collection Interface
 *
 * A Collection is an object that contains other items which can be set, get and removed from the Collection. This
 * is the base interface which does not propose any means of setting data in the Collection other than the
 * self::replace() method. You should implement one of the more concrete interfaces, like Map or Sequence.
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Collection extends \ArrayAccess, \Countable, \Traversable, core\interfaces\Serializable
{
    /**
     * Removes all items currently present in the Collection and replaces them with the given ones.
     *
     * @param   mixed   $items  An object implementing the Collection interface or any other type which will be
     *                          cast to an array.
     * @return  $this
     */
    public function replace($items) : Collection;

    /**
     * Returns all items contained in the Collection, preserving their keys.
     *
     * @return  array
     */
    public function all() : array;

    /**
     * Returns the first item of the Collection which passes the given truth test.
     *
     * @param   callable    $callback   The truth test the item should pass.
     * @param   mixed       $default    The default value to be returned if none of the items passes the test.
     * @return  mixed                   The first item which passed the truth test.
     */
    public function find(callable $callback, $default = null);

    /**
     * Returns the first item of the Collection,
     *   OR the first $callback items of the Collection when $callback is a number,
     *   OR the first item which passes the given truth test when $callback is a callable.
     *
     * @param   callable|int|bool   $callback   The truth test the item should pass or an integer denoting how many
     *                                          of the initial item of the Collection should be returned.
     *                                          When a falsy value is given, the method will return the first
     *                                          item of the Collection.
     * @param   mixed               $default    The default value to be returned if none of the items passes
     *                                          the test or the Collection is empty.
     * @return  mixed
     */
    public function first($callback = false, $default = null);

    /**
     * Returns the last item of the Collection,
     *   OR the final $callback items of the Collection when $callback is a number,
     *   OR the last item which passes the given truth test when $callback is a callable.
     *
     * @param   callable|int|bool   $callback   The truth test the item should pass or an integer denoting how many
     *                                          of the final elements of the Collection should be returned.
     *                                          When a falsy value is given, the method will return the last
     *                                          item of the Collection.
     * @param   mixed               $default    The default value to be returned if none of the items passes
     *                                          the test or the Collection is empty.
     * @return  mixed
     */
    public function last($callback = false, $default = null);

    /**
     * Returns all but the last item of the Collection,
     *   OR all but the last $callback items if $callback is a number,
     *   OR all but the last items for which the $callback returns true if $callback is a callable,
     *
     * @param   callable|int|bool   $callback   The truth test the items should pass or an integer denoting how many
     *                                          of the final items of the Collection should be excluded.
     *                                          When a falsy value is given, the method will return all but the
     *                                          last item of the array.
     * @param   mixed               $default    The default value to be returned if none of the items passes the
     *                                          test or the Collection contains no more than one item.
     * @return  mixed
     */
    public function initial($callback = false, $default = null);

    /**
     * Returns all but the first item of the Collection,
     *   OR all but the first $callback items if $callback is a number,
     *   OR all but the first items for which the $callback returns true if $callback is a callable,
     *
     * @param   callable|int|bool   $callback   The truth test the items should pass or an integer denoting how many
     *                                          of the initial items of the Collection should be excluded.
     *                                          When a falsy value is given, the method will return all but the
     *                                          first item of the array.
     * @param   mixed               $default    The default value to be returned if none of the items passes the
     *                                          test or the Collection contains no more than one item.
     * @return  mixed
     */
    public function rest($callback = false, $default = null);

    /**
     * Returns a slice of the Collection as a new Collection instance.
     *
     * @param   int     $offset         If the offset is non-negative, the slice will start at that offset in the
     *                                  Collection. If it is negative, the sequence will start as far as $offset
     *                                  from the end of the Collection..
     * @param   int     $length         If length is given and positive, then the slice will have up to that
     *                                  many items in it. If the Collection is shorter than the given length,
     *                                  then only the available items will be present. If length is given
     *                                  and is negative, then the slice will stop that many items from the end
     *                                  of the Collection. If it is omitted, then the slice will have everything
     *                                  from the $offset up until the end of the Collection.
     * @param   bool    $preserveKeys   Whether to preserve the original keys. Defaults to true.
     * @return  Collection              A new Collection instance.
     */
    public function slice(int $offset, int $length = null, bool $preserveKeys = true) : Collection;

    /**
     * Looks for the value with the given key/property of $key inside the Collection and returns a new array
     * containing all values of said key from the initial array. Essentially like fetching a column from a
     * classic database table.
     *
     * When the optional $index parameter is given, the resulting array will be indexed by the values corresponding
     * to the given $key.
     *
     * @param   string|int  $key    The key of the value to look for.
     * @param   string|int  $index  The key of the value to index the resulting array by.
     * @return  array
     */
    public function pluck($key, $index = null) : array;

    /**
     * Runs a filter over each of the items in the Collection and returns a new Collection with the filtered
     * results. Excludes all items from the new Collection for which the $filter returns false.
     *
     * @param   callable    $filter     The filter to use.
     * @return  Collection              A new Collection instance.
     */
    public function select(callable $filter) : Collection;

    /**
     * Runs a filter over each of the items in the Collection and returns a new Collection with the filtered
     * results. Excludes all items from the new Collection for which the $filter returns true.
     *
     * @param   callable    $filter     The filter to use.
     * @return  Collection              A new Collection instance.
     */
    public function reject(callable $filter) : Collection;

    /**
     * Executes a callback over each of the items in the Collection and returns a new Collection based on the
     * return values of the callback.
     *
     * @param   callable    $callback   The callable to execute over each item.
     * @return  Collection              A new Collection instance.
     */
    public function map(callable $callback) : Collection;

    /**
     * Executes a callback over each of the items in the Collection but ignores the return values of the callback.
     * The callback will receive 2 arguments - the item and its key, in that order.
     *
     * @param   callable    $callback   The callable to execute over each item.
     * @return  $this
     */
    public function each(callable $callback) : self;

    /**
     * Reduces the items in the Collection to a single value using a callback and returns the resulting value.
     * Goes from the first item to the last, ie. left to right.
     *
     * @param   callable    $callback   The callback to use to reduce the Collection to a single value.
     * @param   mixed       $initial    When given, the value will be used for the first iteration and as a result
     *                                  if the Collection is empty.
     * @return  mixed                   The resulting value.
     */
    public function reduce(callable $callback, $initial = null);

    /**
     * Concatenates the values of a given key of the items in the Collection as a string.
     *
     * @param   string|int  $key    The key of the value to concatenate.
     * @param   string      $glue   The glue to use between the values.
     * @return  string              The concatenated string.
     */
    public function implode($key, string $glue = '') : string;

    /**
     * Reverses the order of the items in the Collection and returns a new Collection with the reversed
     * contents.
     *
     * @return  Collection      A new Collection instance.
     */
    public function reverse() : Collection;

    /**
     * Collapses the items in the Collection into a single array and returns a new Collection based thereon.
     *
     * @return  Collection      A new Collection instance.
     */
    public function collapse() : Collection;

    /**
     * Flattens the Collection and returns a new Collection with the flattened items.
     *
     * @return  Collection      A new Collection instance.
     */
    public function flatten() : Collection;

    /**
     * Fetches a flattened array of an item nested in the Collection.
     *
     * @param   string|int      $key    The key of the item to fetch.
     * @return  Collection              A new Collection instance.
     */
    public function fetch($key) : Collection;

    /**
     * Merges the given items with the items from this Collection and returns a new Collection with the
     * merged results. The indexing rules from array_merge() apply here.
     *
     * @param   Collection[]|array[]    ...$with    The items to merge with this Collection.
     * @return  Collection                          A new Collection instance.
     */
    public function merge(...$with) : Collection;

    /**
     * Diff the collection with the given items.
     *
     * @param   Collection[]|array[]    ...$against The items to diff against.
     * @return  Collection                          A new Collection instance.
     */
    public function diff(...$against) : Collection;

    /**
     * Checks whether the Collection is empty.
     *
     * @return  bool    True if the Collection is empty, false otherwise.
     */
    public function isEmpty() : bool;
}
