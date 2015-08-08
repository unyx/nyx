<?php namespace nyx\core\interfaces;

/**
 * Serializable Interface
 *
 * A Serializable object within Nyx is one that can be cast to an array, string, JSON string and serialized.
 *
 * @package     Nyx\Core
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/core/index.html
 */
interface Serializable extends \Serializable, \JsonSerializable, Arrayable, Jsonable, Stringable
{

}
