<?php namespace nyx\utils;

/**
 * Random
 *
 * Utilities for generating and dealing with (pseudo-)random values.
 *
 * If you need an utility for generating random/fake real-world data,
 * you should take a look at Faker {@see https://github.com/fzaninotto/Faker}
 *
 * @package     Nyx\Utils
 * @version     0.0.5
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/utils/random.html
 */
class Random
{
    /**
     * The traits of the Str class.
     */
    use traits\StaticallyExtendable;

    /**
     * Generates a sequence of pseudo-random bytes of the given $length.
     *
     * Note: This is just a wrapper for random_bytes() provided for completeness of the API.
     *       However, as opposed to the native function simply returning false and raising a warning,
     *       we bump this up to an Exception to be on the safe side.
     *       Please {@see http://php.net/manual/en/function.random-bytes.php} for when this may be the case.
     *
     * @param   int     $length             The length of the random string of bytes that should be generated.
     * @return  string                      The resulting string in binary format.
     * @throws  \InvalidArgumentException   When a expected length smaller than 1 was given.
     * @throws  \RuntimeException           When the platform specific RNG couldn't be used for some reason.
     */
    public static function bytes(int $length) : string
    {
        if ($length < 1) {
            throw new \InvalidArgumentException('The expected amount of random bytes must be at least 1.');
        }

        if (false === $result = random_bytes($length)) {
            throw new \RuntimeException('No sufficient source of randomness is available on this platform.');
        }

        return $result;
    }

    /**
     * Generates a pseudo-random integer in the specified range. {0 .. PHP_INT_MAX} by default.
     *
     * Note: This is just a wrapper for random_bytes() provided for completeness of the API.
     *       However, as opposed to the native function simply returning false and raising a warning,
     *       we bump this up to an Exception to be on the safe side.
     *       Please {@see http://php.net/manual/en/function.random-int.php} for when this may be the case.
     *
     * @param   int     $min                The minimal expected value of the generated integer (>= than PHP_INT_MIN).
     * @param   int     $max                The maximal expected value of the generated integer (<= than PHP_INT_MAX).
     * @return  int                         The generated integer.
     * @throws  \InvalidArgumentException   When the minimal expected value is bigger than the maximal expected value.
     * @throws  \RuntimeException           When the platform specific RNG couldn't be used for some reason.
     */
    public static function int(int $min = 0, int $max = PHP_INT_MAX) : int
    {
        if ($min > $max) {
            throw new \DomainException('The minimal expected value must be smaller than the maximal expected value.');
        }

        if (false === $result = random_int($min, $max)) {
            throw new \RuntimeException('No sufficient source of randomness is available on this platform.');
        }

        return $result;
    }

    /**
     * Generates a pseudo-random float in the specified range
     *
     * @param   int     $min        The minimal value of the generated float. Must be >= than PHP_INT_MIN.
     * @param   int     $max        The maximal value of the generated float. Must be <= than PHP_INT_MAX.
     * @return  float               The generated float.
     * @throws  \InvalidArgumentException   When the minimal expected value is bigger than the maximal expected value.
     */
    public static function float(int $min = 0, int $max = 1) : float
    {
        if ($min > $max) {
            throw new \DomainException('The minimal expected value must be smaller than the maximal expected value.');
        }

        return $min + static::int() / PHP_INT_MAX * ($max - $min);
    }

    /**
     * Generates a pseudo-random string of the specified length using random alpha-numeric (base64)
     * characters or the characters provided.
     *
     * Triggers an E_USER_NOTICE error if a $characters list containing only one character is given
     * while at the same time expecting a generated string with a $length > 1, since this results
     * in repeating that character $length number of times and is a dangerous op in a cryptographic
     * context.
     *
     * Aliases:
     *  - @see Str::random()
     *
     * @param   int     $length             The expected length of the generated string.
     * @param   string  $characters         The character list to use. If not given, the method will
     *                                      fall back to the Base64 character set.
     * @return  float                       The generated string.
     * @throws  \InvalidArgumentException   When a expected length smaller than 1 was given.
     */
    public static function string(int $length = 8, string $characters = null) : string
    {
        if ($length < 1) {
            throw new \InvalidArgumentException('The expected length of the generated string must be at least 1.');
        }

        // Expected most common use case is using the base64 character set, ie. when no
        // character list was given, so let's handle it right away.
        if (empty($characters)) {
            $bytes = static::bytes((int) ceil($length * 0.75));

            return substr(rtrim(base64_encode($bytes), '='), 0, $length);
        }

        // If only a single character was given...
        if (1 === $charactersLen = strlen($characters)) {

            // ... and we only expected one to be generated, d'oh, we're gonna return it.
            if ($charactersLen === $length) {
                return $characters;
            }

            // Since this might be done in a cryptographic context, at least be sassy about it
            // and notify the user that we do not find this amusing.
            trigger_error('Attempted to generate a random string of '.$length.' characters but was given only 1 character to create it out of. This is potentially unsafe.');

            // We're gonna repeat it $length times in a *totally random* order, d'oh.
            return str_repeat($charactersLen, $length);
        }

        $result = '';
        $bytes  = static::bytes($length);
        $pos    = 0;

        // Generate one character at a time until we reach the expected length.
        for ($idx = 0; $idx < $length; $idx++) {
            $pos     = ($pos + ord($bytes[$idx])) % $charactersLen;
            $result .= $characters[$pos];
        }

        return $result;
    }

    /**
     * Generates a pseudo-random boolean value.
     *
     * @return  bool    The resulting boolean.
     */
    public static function bool() : bool
    {
        return (bool) (ord(static::bytes(1)) % 2);
    }
}