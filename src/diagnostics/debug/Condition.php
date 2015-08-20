<?php namespace nyx\diagnostics\debug;

/**
 * Condition
 *
 * The code which should be used both for matching and for execution on match can be set at runtime, either using
 * the constructor or the respective setters (and therefore could also easily be overridden). Theoretically one
 * could execute 'onMatch' logic directly within the matcher, but it is advised to separate them. Likewise,
 * setting either without setting its counterpart makes no sense, especially for 'onMatch' since it won't be called
 * at all unless this Condition is met.
 *
 * For complex Conditions you may want to override {@see self::matches()} and {@see self::onMatch()} within a Class
 * that inherits from this Condition to keep your codebase legible.
 *
 * Conditions have to be applied to Handlers before the flow gets automated. You can either use a Condition's
 * or a Handler's apply() method directly. "Virtual" Conditions can also be applied to Handlers (those
 * are pairs of matches/onMatch callables but given without instantiating a Condition object, which equals less
 * overhead). Please see the Handler's documentation for more on that.
 *
 * @package     Nyx\Diagnostics\Debug
 * @version     0.0.5
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/diagnostics/debug.html
 */
class Condition
{
    /**
     * @var callable    The code that should be run when this Condition is met.
     */
    private $onMatch;

    /**
     * @var callable    The code that should be used to check whether this Condition is met.
     */
    private $matcher;

    /**
     * Constructs the Condition.
     *
     * @param   callable    $matches    The code which should be used in order to check whether this Condition
     *                                  matches a given Exception.
     * @param   callable    $onMatch    The code which should be executed when this Condition is met.
     */
    public function __construct(callable $matches = null, callable $onMatch = null)
    {
        if (null !== $matches) {
            $this->setMatcher($matches);
        }

        if (null !== $onMatch) {
            $this->setOnMatch($onMatch);
        }
    }

    /**
     * Executes code in order to check whether this Condition matches the given Exception.
     *
     * Note: Not declared abstract because a callable may be given at runtime to be executed (either using the
     * constructor or the respective setter), but you may want to simply override this for complex Conditions and
     * store your Condition as a separate class to keep your codebase legible.
     *
     * @param   \Exception  $exception  The Exception which should be tested.
     * @param   Handler     $handler    The Handler which is running the match.
     * @return  bool                    True when this Condition is met, false otherwise.
     */
    public function matches(\Exception $exception, Handler $handler) : bool
    {
        return $this->matcher ? call_user_func($this->matcher, $exception, $handler) : false;
    }

    /**
     * Sets the code which should be used in order to check whether this Condition matches a given Exception.
     *
     * @param   callable    $code
     * @return  $this
     */
    public function setMatcher(callable $code) : self
    {
        $this->matcher = $code;

        return $this;
    }

    /**
     * Executes code that applies when this Condition is met.
     *
     * Note: Not declared abstract because a callable may be given at runtime to be executed (either using the
     * constructor or the respective setter), but you may want to simply override this for complex Conditions and
     * store your Condition as a separate class to keep your codebase legible.
     *
     * @param   \Exception  $exception  The Exception which has been matched.
     * @param   Handler     $handler    The Handler which ran the match.
     * @return  int|null                One of definitions\Signals or null.
     */
    public function onMatch(\Exception $exception, Handler $handler)
    {
        return $this->onMatch ? call_user_func($this->onMatch, $exception, $handler) : null;
    }

    /**
     * Sets the code which should be executed when this Condition is met.
     *
     * @param   callable    $code
     * @return  $this
     */
    public function setOnMatch(callable $code) : self
    {
        $this->onMatch = $code;

        return $this;
    }

    /**
     * Applies this Condition to the given Handler.
     *
     * @param   Handler $handler    The Handler this Condition should be applied to.
     * @return  $this
     */
    public function apply(Handler $handler) : self
    {
        $handler->apply($this);

        return $this;
    }
}
