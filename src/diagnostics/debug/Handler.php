<?php namespace nyx\diagnostics\debug;

// External dependencies
use nyx\events;

// Internal dependencies
use nyx\diagnostics\definitions;

/**
 * Abstract Handler
 *
 * Provides means for concrete Handlers to deal with Conditions and debug Events. Does not actually allow for
 * the implementation of a specific handler interface on its own as it does not implement either of the handle()
 * methods.
 *
 * @package     Nyx\Diagnostics\Debug
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/diagnostics/debug.html
 */
abstract class Handler implements events\interfaces\EmitterAware
{
    /**
     * The traits of a Handler instance.
     */
    use events\traits\EmitterAware;

    /**
     * @var Condition[]         An array of Condition instances that will be checked before handling the
     *                          Error/Exception or arrays containing 'matcher' and 'onMatch' keys with callables
     *                          as values. See {@see self::apply()} for more information.
     */
    private $conditions = [];

    /**
     * @var bool    Whether Conditions/Delegates are allowed to arbitrarily end script execution by returning
     *              definitions/Signals::QUIT.
     */
    private $allowQuit = true;

    /**
     * Applies a Condition to this Handler.
     *
     * @param   Condition|callable  $condition  Either a Condition instance or a 'matcher' callable accepting one
     *                                          two arguments - an Exception and a Handler instance, and
     *                                          returning true/false when the given Exception is a match or not.
     *                                          When a callable is given, the second argument to this method must
     *                                          also be given.
     * @param   callable            $onMatch    A callable containing the code that should be executed when the
     *                                          'matcher' callable given as first argument returns true. This
     *                                          argument is ignored when a concrete Condition instance is given
     *                                          as the first argument instead of a callable.
     * @return  $this
     * @throws  \InvalidArgumentException       When a callable is given as first argument but the second is
     *                                          missing or when neither a Condition instance nor a callable are
     *                                          given as the first argument.
     */
    public function apply($condition, callable $onMatch = null) : self
    {
        $callable = is_callable($condition);

        if (!$callable && !$condition instanceof Condition) {
            throw new \InvalidArgumentException('The first parameter given must be a \nyx\diagnostics\Condition instance or a callable. ['.gettype($condition).'] given.');
        }

        // Condition instances.
        if (!$callable) {
            $this->conditions[] = $condition;
        }
        // Both parameters are callables.
        else {
            if (null === $onMatch) {
                throw new \InvalidArgumentException('A callable must be given as second parameter when the first is also a callable.');
            }

            $this->conditions[] = [
                'matcher' => $condition,
                'onMatch' => $onMatch
            ];
        }

        return $this;
    }

    /**
     * Sets whether Conditions/Delegates are allowed to arbitrarily end script execution by returning
     * definitions/Signals::QUIT.
     *
     * @param   bool    $bool   True to allow Conditions/Delegates to end script execution, false otherwise.
     */
    public function setAllowQuit(bool $bool)
    {
        $this->allowQuit = $bool;
    }

    /**
     * Checks whether Conditions/Delegates are allowed to arbitrarily end script execution by returning
     * definitions/Signals::QUIT.
     *
     * @return  bool    True when Conditions/Delegates are allowed to end script execution, false otherwise.
     */
    public function doesAllowQuit() : bool
    {
        return $this->allowQuit;
    }

    /**
     * Runs through the registered Conditions and invokes their callbacks when they match the given Exception.
     *
     * @param   \Exception  $exception  The Exception conditions should match
     * @return  bool                    True when any Condition returns the PREVENT signal, false otherwise.
     */
    protected function runConditions(\Exception $exception) : bool
    {
        $prevent = false;

        foreach ($this->conditions as $condition) {
            // We can call the methods on a Condition instance directly.
            if ($condition instanceof Condition) {
                if (true === $condition->matches($exception, $this)) {
                    $response = $condition->onMatch($exception, $this);
                } else {
                    continue;
                }
            }
            // Otherwise we're dealing with our little 'array' condition, ie. two callables. Run the match straight
            // away.
            elseif (true === call_user_func($condition['matcher'], $exception, $this)) {
                $response = call_user_func($condition['onMatch'], $exception, $this);
            } else {
                continue;
            }

            // Now let's check what onMatch() returned and see if it's a QUIT and we may exit.
            if (($response & definitions\Signals::QUIT) === definitions\Signals::QUIT and $this->allowQuit) {
                exit;
            }

            // Using the PREVENT signal on its own will not break the loop but we will need to pass it to the Handler
            // afterwards so it knows that it shouldn't proceed with its own code.
            if (($response & definitions\Signals::PREVENT) === definitions\Signals::PREVENT) {
                $prevent = true;
            }

            // QUIT includes STOP so this will catch both situations.
            if (($response & definitions\Signals::STOP) === definitions\Signals::STOP) {
                break;
            }
        }

        return $prevent;
    }

    /**
     * Helper method which emits a diagnostics\events\Debug event with the given name and the given initial
     * Exception and returns the Exception set in the Event after emission is done. All of it assuming an Emitter
     * is set for the Handler. False will be returned if that is not the case.
     *
     * @param   string          $name       The name of the Event to emit {@see definitions/Events}.
     * @param   \Exception      $exception  The initial Exception to be passed to listeners.
     * @return  \Exception|null             Either an Exception when event emission occurred or null if no Emitter
     *                                      is set and therefore no events were emitted.
     */
    protected function emitDebugEvent($name, \Exception $exception)
    {
        // Don't proceed when we've got no Emitter.
        if (null === $this->emitter) {
            return null;
        }

        $this->emitter->emit($name, $event = new Event($exception, $this));

        // Event Listeners may override the Exception. Need to account for that.
        return $event->getException();
    }
}
