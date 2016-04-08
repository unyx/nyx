<?php namespace nyx\utils;

// External dependencies
use nyx\core;

/**
 * Random
 *
 * Utilities for generating and dealing with (pseudo-)random values.
 *
 * --- Strength
 *
 * All methods in this class can be called with a strength setting, being one of the STRENGTH_* class
 * constants and STRENGTH_MEDIUM being the default for each method.
 *
 *   - STRENGTH_STRONG is cryptographically secure but may be the slowest setting;
 *   - STRENGTH_MEDIUM is cryptographically secure and can be safely used for generating keys and salts;
 *   - STRENGTH_WEAK is *not* cryptographically secure but should be used in a non-crypto context for
 *     generating randomized values (it's the fastest out of the settings);
 *   - STRENGTH_NONE is *absolutely not* cryptographically secure - it should only be used is a context
 *     with absolutely no relation to encryption or authentication. Currently it only affects Random::string()
 *     in that this method will use str_shuffle() instead of generating a string based on a stronger
 *     pseudo-random seed (which is, simply, considerably faster);
 *
 * Important note: This class *is not* a cryptography class and does not perform any sort of mixing
 * of the generated values. For stronger input vectors for actual encryption algorithms you may want
 * to employ a mixing strategy for the data generated by this class before signing it off.
 *
 * In some situations, for instance when a weak random integer is sufficient, for performance reasons you
 * may be better off simply using mt_rand() (which is also used by self::int() if called with STRENGTH_WEAK) -
 * this class does however provide a consistent API and error handling to ensure nothing silently fails
 * leading to security holes.
 *
 * --- Sources
 *
 * This class is primarily a strict wrapper around PHP's native random_bytes() and random_int() functions,
 * which make use of the following sources:
 *   - Windows: CryptGenRandom() (only)
 *   - Others: getrandom(2) syscall (Linux only), then /dev/urandom
 *
 * The first valid source in those orders gets used. In the edge case where that procedure fails,
 * this class throws exceptions does but attempts two (by default) additional fallbacks first (in this order):
 *   - openssl_random_pseudo_bytes(), if available (which uses a userspace hash algo making it
 *     potentially an additional point of failure and thus only valid for the STRENGTH_MEDIUM setting
 *     and below);
 *   - mcrypt_create_iv(), if available (which basically uses the exact same sources as random_bytes()
 *     although in a slightly different execution process, so it's merely a second attempt at doing what
 *     random_bytes() should've done);
 *
 * No additional userspace entropy sources are used nor introduced by this utility. Libsodium may be
 * introduced as additional primary fallback at a later date.
 *
 * The fallback mechanism is lazily instantiated - unless PHP's native functions fail us, there will
 * be minimal overhead of using this class over random_bytes() directly, but with added verbosity.
 *
 * --- Others
 *
 * Based on Anthony Ferrara's work on RandomLib {@see https://github.com/ircmaxell/RandomLib}.
 *
 * If you need an utility for generating random/fake real-world data, you should take a look
 * at Faker {@see https://github.com/fzaninotto/Faker}. This functionality is beyond the scope
 * of this utility.
 *
 * @package     Nyx\Utils
 * @version     0.0.6
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/utils/random.html
 * @todo        Random::password() utility.
 */
class Random
{
    /**
     * The traits of the Str class.
     */
    use traits\StaticallyExtendable;

    /**
     * Strength constants. The default for all methods is STRENGTH_MEDIUM. Consult the class description
     * for more information on when and how to use these constants.
     */
    const STRENGTH_NONE    = 1;
    const STRENGTH_WEAK    = 3;
    const STRENGTH_MEDIUM  = 5;
    const STRENGTH_STRONG  = 7;

    /**
     * @var array   A map of Source classes grouped together by their STRENGTH_*. Remains null until an edge-case
     *              is hit and this class needs to fall back to non-native entropy sources.
     *
     * @see Random::fallbackBytes()
     * @see Random::getSources()
     */
    protected static $sources;

    /**
     * Generates a sequence of pseudo-random bytes of the given $length.
     *
     * @param   int     $length             The length of the random string of bytes that should be generated.
     * @param   int     $strength           The requested strength of entropy (one of the STRENGTH_* class constants)
     * @return  string                      The resulting string in binary format.
     * @throws  \InvalidArgumentException   When a expected length smaller than 1 was given.
     */
    public static function bytes(int $length, int $strength = self::STRENGTH_MEDIUM) : string
    {
        if ($length < 1) {
            throw new \InvalidArgumentException('The expected number of random bytes must be at least 1.');
        }

        // For any strength above the lowest we are gonna rely on sources with proper entropy.
        // Note that on platforms with a full Suhosin patch mt_rand() isn't actually *that* weak.
        if ($strength > self::STRENGTH_WEAK) {
            try {
                return random_bytes($length);
            } catch (\Exception $exception) {
                // Try a fallback if native PHP failed us. The fallback will also throw an exception if it fails
                // to generate random bytes of sufficient entropy.
                return static::fallbackBytes($length, $strength);
            }
        }

        // At STRENGTH_WEAK or lower we will simply fall back to mt_rand().
        // Note that on platforms with a full Suhosin patch mt_rand() isn't actually *that* weak.
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $result .= chr((mt_rand() ^ mt_rand()) % 256);
        }

        return $result;
    }

    /**
     * Generates a pseudo-random integer in the specified range, {0 .. PHP_INT_MAX} by default.
     * The range is inclusive.
     *
     * The arguments can be passed in in any order. The resulting range must be <= PHP_INT_MAX and neither of the
     * arguments may exceed PHP_INT_MIN nor PHP_INT_MAX.
     *
     * @param   int     $min                The minimal expected value of the generated integer (>= than PHP_INT_MIN).
     * @param   int     $max                The maximal expected value of the generated integer (<= than PHP_INT_MAX).
     * @param   int     $strength           The requested strength of entropy (one of the STRENGTH_* class constants)
     * @return  int                         The generated integer.
     * @throws  \RangeException             When the specified range is invalid.
     */
    public static function int(int $min = 0, int $max = PHP_INT_MAX, int $strength = self::STRENGTH_MEDIUM) : int
    {
        // Allow for passing in the range in reverse order.
        $tmp   = max($min, $max);
        $min   = min($min, $max);
        $max   = $tmp;
        $range = $max - $min;

        if ($range === 0) {
            return $max;
        }

        // A range < 0 shouldn't happen at this point but may denote an arithmetic error.
        if ($range < 0 || $range > PHP_INT_MAX) {
            throw new \RangeException('The supplied range is too broad to generate a random integer from.');
        }

        // For any strength above the lowest we are gonna rely on sources with proper entropy.
        if ($strength > self::STRENGTH_WEAK) {
            try {
                return random_int($min, $max);
            } catch (\Exception $exception) {
                // Note: We're not checking for entropy sources here. self::fallbackInt() makes use of self::bytes()
                // so the exception will be thrown there if no pseudo-random bytes could be generated or in
                // self::fallbackInt() itself when the result is not a valid integer in the requested range.
                return static::fallbackInt($min, $max, $strength);
            }
        }

        // At STRENGTH_WEAK or lower we will simply fall back to mt_rand().
        // Note that on platforms with a full Suhosin patch mt_rand() isn't actually *that* weak.
        return mt_rand($min, $max);
    }

    /**
     * Generates a pseudo-random float in the specified range, {0 .. 1} by default. The range is inclusive.
     *
     * The arguments can be passed in in any order. The resulting range must be <= PHP_INT_MAX and neither of the
     * arguments may exceed PHP_INT_MIN nor PHP_INT_MAX.
     *
     * Note: You can pass in integers instead of floats for $min and $max, since PHP will perform a widening
     * conversion on them. as long as they are above and below PHP_INT_MIN and PHP_INT_MAX respectively.
     *
     * @param   float   $min                The minimal value of the generated float. Must be >= than PHP_INT_MIN.
     * @param   float   $max                The maximal value of the generated float. Must be <= than PHP_INT_MAX.
     * @param   int     $strength           The requested strength of entropy (one of the STRENGTH_* class constants)
     * @return  float                       The generated float.
     * @throws  \RangeException             When the specified range is invalid.
     */
    public static function float(float $min = 0, float $max = 1, int $strength = self::STRENGTH_MEDIUM) : float
    {
        // Allow for passing in the range in reverse order.
        $tmp   = max($min, $max);
        $min   = min($min, $max);
        $max   = $tmp;
        $range = $max - $min;

        if ($range === 0) {
            return $max;
        }

        // A range < 0 shouldn't happen at this point but may denote an arithmetic error.
        if ($range < 0 || $range > PHP_INT_MAX) {
            throw new \RangeException('The supplied range is too broad to generate a random floating point number from.');
        }

        return $min + static::int(0, PHP_INT_MAX, $strength) / PHP_INT_MAX * $range;
    }

    /**
     * Generates a pseudo-random boolean value.
     *
     * @return  bool    The resulting boolean.
     */
    public static function bool(int $strength = self::STRENGTH_MEDIUM) : bool
    {
        return (bool) (ord(static::bytes(1, $strength)) % 2);
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
     * Note: Does *not* support multi-byte characters!
     *
     * Aliases:
     *  - @see Str::random()
     *
     * @param   int         $length         The expected length of the generated string.
     * @param   string|int  $characters     The character list to use. Can be either a string
     *                                      with the characters to use or an int | nyx\core\Mask
     *                                      to generate a list (@see utils\str\Character::buildSet()).
     *                                      If not provided or an invalid mask, the method will fall
     *                                      back to the Base64 character set.
     * @param   int         $strength       The requested strength of entropy (one of the STRENGTH_* class constants)
     * @return  string                      The generated string.
     * @throws  \InvalidArgumentException   When a expected length smaller than 1 was given.
     */
    public static function string(int $length = 8, $characters = str\Character::CHARS_BASE64, int $strength = self::STRENGTH_MEDIUM) : string
    {
        if ($length < 1) {
            throw new \InvalidArgumentException('The expected length of the generated string must be at least 1.');
        }

        if (is_int($characters) || $characters instanceof core\Mask) {
            $characters = str\Character::buildSet($characters);
        }

        // Fall back to the Base64 character set if necessary.
        if (empty($characters)) {
            $characters = str\Character::buildSet(str\Character::CHARS_BASE64);
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

        // With a STRENGTH_NONE (exclusively) setting we will simply shuffle the characters.
        // This is faster but not doesn't come close to random. Every higher setting will go through
        // the process of getting a random seed of the specified strength and actually generating
        // the string.
        if (self::STRENGTH_NONE) {
            return substr(str_shuffle(str_repeat($characters, $length)), 0, $length);
        }

        $result = '';
        $bytes  = static::bytes($length, $strength);
        $pos    = 0;

        // Generate one character at a time until we reach the expected length.
        // @todo Benchmark for faster/less predictable implementations.
        for ($idx = 0; $idx < $length; $idx++) {
            $pos     = ($pos + ord($bytes[$idx])) % $charactersLen;
            $result .= $characters[$pos];
        }

        return $result;
    }

    /**
     * Generates a sequence of pseudo-random bytes of the given $length from fallback sources if the
     * default implementation in self::bytes() failed for any reason. The sources used are returned
     * by self::getSources() and this mechanism respects the requested entropy strength (ie. stronger
     * sources may satisfy the request but weaker sources won't).
     *
     * Note: Does *not* check whether $length is valid - intended to be called internally by self::bytes()
     *       which does perform all relevant checks (and throws exceptions), so take this into account when overriding.
     *
     * @param   int     $length     The length of the random string of bytes that should be generated.
     * @param   int     $strength   The requested strength of entropy (one of the STRENGTH_* class constants)
     * @return  string              The resulting string in binary format or an empty string if no actual
     *                              random bytes were generated.
     * @throws  \DomainException    When one of the sources returned by self::getSources() has either no
     *                              specified 'class' value or that value points to a class which is not
     *                              an instance of random\interfaces\Source.
     * @throws  \RuntimeException   When pseudo-random bytes of the requested entropy could not be generated
     *                              (most likely due to lack of appropriate sources).
     */
    protected static function fallbackBytes(int $length, int $strength) : string
    {
        foreach (static::getSources() as $sourceStrength => &$sources) {

            // A lower strength requirement can be satisfied by higher strength sources - as such
            // the order of the sources matters.
            if ($sourceStrength < $strength) {
                continue;
            }

            foreach ($sources as &$source) {
                // If no instance was made yet, we will try to create one it we meet its dependencies.
                if(!isset($source['instance'])) {

                    if(!isset($source['class']) || !$source['class'] instanceof random\interfaces\Source) {
                        throw new \DomainException('Sources must have a specified [class] key pointing to a class implementing nyx\utils\random\interfaces\Source');
                    }

                    // We have to pass to the next available source if we can't meet the requirements
                    // of this one.
                    if(isset($source['dependsOn']) && !function_exists($source['dependsOn'])) {
                        continue;
                    }

                    $source['instance'] = new $source['class'];
                }

                // First valid result is good enough to return. Any failed attempts will be handled
                // by exceptions being thrown by Source::generate() itself and will prevent the return.
                try {
                    return $source['instance']->generate($length);
                } catch(\RuntimeException $exception) {
                    // Ignoring the Exception since we handled it by not returning any valid result.
                }
            }
        }

        // At this stage we didn't get a single valid result.
        throw new \RuntimeException('No source with sufficient entropy is available on this platform.');
    }

    /**
     * Generates a pseudo-random integer in the specified range from fallback sources if the default
     * implementation in self::int() failed for any reason.
     *
     * Note: Does *not* check whether the specified range is valid for integers - intended to be called
     *       internally by self::int() which does perform all relevant checks (and throws exceptions), so take
     *       this into account when overriding.
     *
     * @param   int     $min        The minimal expected value of the generated integer (>= than PHP_INT_MIN).
     * @param   int     $max        The maximal expected value of the generated integer (<= than PHP_INT_MAX).
     * @param   int     $strength   The requested strength of entropy (one of the STRENGTH_* class constants)
     * @return  int                 The generated integer.
     * @throws  \RuntimeException   When failing to generate a pseudo-random integer in the specified range.
     */
    protected static function fallbackInt(int $min, int $max, int $strength) : int
    {
        // Note: No type/value validity checks performed here. We're assuming to be called from self::int()
        // only and don't want to duplicate the checks contained therein.
        $range = $max - $min;

        // We need to count the bits required to represent the range.
        $bits = 0;
        while ($range >>= 1) {
            $bits++;
        }

        // We'll be offsetting the resulting integer to squeeze it into the range
        // and we need some data for that.
        $bits   = max($bits, 1);
        $bytes  = max(ceil($bits / 8), 1);
        $mask   = (1 << $bits) - 1;

        do {
            $result = hexdec(bin2hex(static::bytes($bytes, $strength))) & $mask;
        } while ($result > $range);

        $result = $min + $result;

        // Assert we got a integer in the requested range.
        if (!is_int($result) || $result < $min || $result > $max) {
            throw new \RuntimeException('Failed to generate a random integer in the ['.$min.' - '.$max.'] range. Possibly due to lack of sources with sufficient entropy.');
        }

        return $result;
    }

    /**
     * Returns a list of sources to be used by self::fallbackBytes(), ordered in ascending order
     * by their strength (also for performance reasons) and their priority of evaluation.
     *
     * @see     Random::fallbackBytes()
     * @return  array
     */
    protected static function getSources() : array
    {
        return static::$sources ?? (static::$sources = [
            self::STRENGTH_MEDIUM => [
                [
                    'class'     => 'nyx\utils\random\sources\OpenSSL',
                    'dependsOn' => 'openssl_random_pseudo_bytes'
                ]
            ],
            self::STRENGTH_STRONG => [
                [
                    'class'     => 'nyx\utils\random\sources\Mcrypt',
                    'dependsOn' => 'mcrypt_create_iv'
                ]
            ]
        ]);
    }
}
