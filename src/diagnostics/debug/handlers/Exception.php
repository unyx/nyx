<?php namespace nyx\diagnostics\debug\handlers;

// Internal dependencies
use nyx\diagnostics\debug\interfaces;
use nyx\diagnostics\debug;
use nyx\diagnostics\definitions;

/**
 * Exception Handler
 *
 * Responsible for handling Exceptions that have not get caught. Inspects them and provides its analyses to
 * registered Delegates {@see interfaces\Delegate}, which could be logging facilities, full page error displays
 * and the likes. All collection-like methods (add, remove, all etc.) manage the delegates and are named this way
 * for simplicity's sake.
 *
 * Delegates (both callables and actual Delegate instances) can be named and referred to by the set name for
 * white-and-black-listing and removal. Check {@see self::add()} and {@see self::set()} for information on the
 * naming behaviour.
 *
 * @package     Nyx\Diagnostics\Debug
 * @version     0.0.5
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/diagnostics/debug.html
 * @todo        Buffer the output of delegates within self::handle() and optionally return it instead of sending?
 * @todo        Optional adding of Delegates upon Handler construction?
 */
class Exception extends debug\Handler implements interfaces\handlers\Exception
{
    /**
     * @var array   An array of registered interfaces\Delegate and callables. Each value in the array is an array
     *              containing two key => value pairs: The 'delegate' (callable | interfaces\Delegate) itself,
     *              and the 'priority' (int).
     */
    private $delegates = [];

    /**
     * @var array   The (cached) delegates applicable for the next call to self::handle().
     */
    private $applicable;

    /**
     * @var array   A list of Delegate *names* not allowed to handle the exception currently being handled.
     */
    private $blacklist = [];

    /**
     * @var array   A list of Delegate *names* allowed to handle the exception currently being handled.
     */
    private $whitelist = [];

    /**
     * @var bool    Whether the blacklist overrides the whitelist. Ie. when a given Delegate name is present in
     *              both arrays it will be treated as blacklisted.
     */
    private $prioritizeBlacklist = true;

    /**
     * @var int     The currently highest priority assigned to any Delegate/callable.
     */
    private $highestPriority = 0;

    /**
     * Registers the given or this Exception Handler with PHP.
     *
     * @param   interfaces\handlers\Exception   $handler    An optional, already instantiated Exception Handler
     *                                                      instance. If none is given, a new one will be
     *                                                      instantiated.
     * @return  Exception                                   An instance of the Exception Handler which got registered.
     *                                                      Either the same as the given one or if none was given,
     *                                                      a new instance.
     */
    public static function register(interfaces\handlers\Exception $handler = null) : Exception
    {
        set_exception_handler([$handler ?: $handler = new static, 'handle']);

        return $handler;
    }

    /**
     * {@inheritDoc}
     *
     * Walks through all stacked Delegates in the order of their priority and passes the inspected Exception to
     * them until one of them returns a STOP or QUIT signal as defined in definitions\Signals or no more Delegates
     * are left.
     */
    public function handle(\Throwable $throwable)
    {
        // Being Emitter Aware we are bound to comply to the Events Definition.
        // self::emitDebugEvent() will return null when no Emitter is present. Otherwise we'll get the Exception
        // after it's been processed by Event Listeners so we need to overwrite it here.
        if (null !== $response = $this->emitDebugEvent(definitions\Events::DEBUG_THROWABLE_BEFORE, $throwable)) {
            $throwable = $response;
        }

        // First of all run all Conditions. The method will return true if we are to prevent further execution.
        if ($this->runConditions($throwable)) {
            return;
        }

        // Whether we will quit after the loop. Set to true when one of the Delegates returns a QUIT signal.
        $quit = false;

        // Get the applicable Delegates.
        $delegates = array_intersect_key($this->delegates, array_flip($this->getApplicable()));

        // If we've got anything to call later on, proceed.
        if (!empty($delegates)) {
            // Sort the Delegates by their priority.
            $this->sort($delegates);

            // Inspect the Exception we got, which will give us an Inspector instance we can pass along.
            $inspector = $this->inspect($throwable);

            // Walk through all applicable Delegates and let them handle the Exception until done or one of them
            // returns a STOP/QUIT signal.
            foreach ($delegates as $v) {
                // If we're dealing with a Delegate instance, call its handle() method. If it's a casual callable,
                // call it and pass the Inspector as the only argument.
                $response = $v['delegate'] instanceof interfaces\Delegate
                    ? $v['delegate']->handle($inspector)
                    : call_user_func($v['delegate'], $inspector);

                // Make it easier for inheriting children to act upon the response if it's not a signal.
                $response = $this->handleDelegateResponse($response, $inspector);

                // Let's check if we've got a signal as response. If it's QUIT, we'll set a flag and handle it
                // after the loop.
                if (($response & definitions\Signals::QUIT) === definitions\Signals::QUIT) {
                    $quit = true;
                }

                // QUIT includes STOP so this will catch both situations.
                if (($response & definitions\Signals::STOP) === definitions\Signals::STOP) {
                    break;
                }
            }
        }

        // Now that we are done looping, time to attempt to emit the appropriate Event.
        $this->emitDebugEvent(definitions\Events::DEBUG_THROWABLE_AFTER, $throwable);

        // If we were told to quit and the Handler allows this, do eet.
        if ($quit and $this->doesAllowQuit()) {
            exit;
        }

        // Clear all lists. See the notes for {@see self::whitelist()} and {@see self::blacklist()} why Delegates
        // should be listed, for consistency, by Conditions *only*.
        $this->applicable = null;
        $this->whitelist  = $this->blacklist = [];
    }

    /**
     * Similar to {@see self::add()} but uses a different argument order - the name is required (as it is used
     * as a key) and the method will overwrite the given key (name) if it is already set. The usage of add()
     * instead is recommended.
     *
     * @param   string                          $name       The name of the Delegate.
     * @param   interfaces\Delegate|callable    $delegate   The Delegate to be inserted into the stack.
     * @param   int                             $priority   The priority at which the Delegate should be invoked
     *                                                      when an exception gets handled.
     *                                                      Also {@see self::getHighestPriority()}.
     * @return  $this
     * @throws  \InvalidArgumentException                   When the argument passed is neither a callable nor
     *                                                      a Delegate.
     * @throws  \InvalidArgumentException                   When the name contains invalid characters. Only letters,
     *                                                      digits and backslashes are allowed.
     */
    public function set(string $name, $delegate, int $priority = 0) : self
    {
        // Make sure we've got a type we can work with.
        if (!$delegate instanceof interfaces\Delegate && !is_callable($delegate)) {
            throw new \InvalidArgumentException("Exception handling delegates must be callables or instances of nyx\\diagnostics\\interfaces\\Delegate.");
        }

        // Perform a little check to make sure we'll be able to black/whitelist with a regexp afterwards.
        if (!preg_match('/^[\\\d\w]+$/', $name)) {
            throw new \InvalidArgumentException("Delegate names may only contain letters, digits and backslashes, [$name] given.");
        }

        $this->delegates[$name] = ['delegate' => $delegate, 'priority' => $priority];
        $this->applicable = null;

        // Keep track of the highest priority currently assigned.
        if ($priority > $this->highestPriority) {
            $this->highestPriority = $priority;
        }

        return $this;
    }

    /**
     * Adds the given Delegate with the given optional priority to the stack.
     *
     * @param   interfaces\Delegate|callable    $delegate   The Delegate to be inserted into the stack.
     * @param   string                          $name       The name of the Delegate. Has to be unique. If none is
     *                                                      given, the full (ie. with namespace) classname will be
     *                                                      be for Delegates and "c\{mt_rand()}" for callables.
     *                                                      *Also* has to be unique. In other words - If you add an
     *                                                      instance of the same class (or even the same instance)
     *                                                      multiple times, assign different names.
     * @param   int                             $priority   The priority at which the Delegate should be invoked
     *                                                      when an exception gets handled.
     *                                                      Also {@see self::getHighestPriority()}.
     * @return  $this
     * @throws  \OverflowException                          When the given name (or when not given, the class name)
     *                                                      is already set.
     */
    public function add($delegate, string $name = null, int $priority = 0) : self
    {
        // Which name should we use?
        // Micro-optimization note: mt_rand() turned out to be several times faster than uniqid and somewhat
        // faster than counting the current number of delegates.
        $name = $name ?: ($delegate instanceof interfaces\Delegate ? get_class($delegate) : 'c\\'.mt_rand());

        if (isset($this->delegates[$name])) {
            throw new \OverflowException("A Delegate with the given name [$name] is already set.");
        }

        return $this->set($name, $delegate, $priority);
    }

    /**
     * Removes a specific Delegate or callable from the stack.
     *
     * Important note: This will remove all *instances* of the given Delegate if it passes the strict match unless
     * $all is set to false *or* a name is used instead of an instance as the first argument to this method, since
     * Delegate names are unique within this Handler.
     *
     * @param   string|interfaces\Delegate|callable     $delegate   Either the name of the Delegate/callable or
     *                                                              an actual instance to search for.
     * @param   bool                                    $all        Whether to search for all matches (true) or stop
     *                                                              after the first match (false).
     * @return  $this
     */
    public function remove($delegate, bool $all = true) : self
    {
        // When a string was passed, we will need to distinguish what to compare within our search.
        $name = is_string($delegate) ? $delegate : null;

        foreach ($this->delegates as $k => $v) {
            if (($name and $k === $name) or (!$name and $v['delegate'] === $delegate)) {
                unset($this->delegates[$k]);
                $this->applicable = null;

                if ($name or false === $all) {
                    break;
                }
            }
        }

        return $this;
    }

    /**
     * Adds the given Delegate name to the blacklist.
     *
     * Note: Blacklisting *should* be performed within Conditions as the blacklist will be cleared after each
     * handle() call and therefore the blacklist only applies to the very next call. Normally this shouldn't be
     * an issue as the default assumption is that the Exception Handler only gets invoked for uncaught exceptions,
     * but... well, you catch the drift.
     *
     * @param   string|array    $name   A regex of the Delegates to blacklist or an array of REs. The regex must
     *                                  be provided *without* the delimiters as they will be added automatically
     *                                  (therefore - no special flags etc.).
     * @return  $this
     */
    public function blacklist($name) : self
    {
        return $this->pushToList($this->blacklist, $name);
    }

    /**
     * Adds the given Delegate name to the whitelist.
     *
     * Note: Whitelisting *should* be performed within Conditions as the whitelist will be cleared after each
     * handle() call and therefore the whitelist only applies to the very next call. Normally this shouldn't be
     * an issue as the default assumption is that the Exception Handler only gets invoked for uncaught exceptions,
     * but... well, you catch the drift.
     *
     * @param   string|array    $name   A regex of the Delegates to whitelist or an array of REs. The regex must
     *                                  be provided *without* the delimiters as they will be added automatically
     *                                  (therefore - no special flags etc.).
     * @return  $this
     */
    public function whitelist($name) : self
    {
        return $this->pushToList($this->whitelist, $name);
    }

    /**
     * Returns all registered Delegates, in the order they were added. See {@see self::$delegates} for more info
     * on the structure.
     *
     * @return  array
     */
    public function all() : array
    {
        return $this->delegates;
    }

    /**
     * Returns an array containing the names of all Delegates which are applicable for the next call to
     * self::handle(), ie. after computing which of them are black-or-whitelisted.
     *
     * @return  array
     */
    public function getApplicable() : array
    {
        // If we've already compiled the list and nothing changed, return it.
        if (null !== $this->applicable) {
            return $this->applicable;
        }

        $delegates = array_keys($this->delegates);

        // No need for any fancy magic if we've got no black/whitelist.
        if (empty($this->whitelist) && empty($this->blacklist)) {
            return $this->applicable = $delegates;
        }

        // Compile the lists by performing regex matches over the existing Delegate names.
        if (!empty($this->whitelist)) {
            $whitelist = $this->compileList($this->whitelist, $delegates);
        }

        if (!empty($this->blacklist)) {
            $blacklist = $this->compileList($this->blacklist, $delegates);
        }

        // No need to proceed further if we didn't actually black/whitelist any currently added Delegates.
        if (empty($whitelist) && empty($blacklist)) {
            return $this->applicable = $delegates;
        }

        // Further checks.
        if (empty($whitelist)) {
            return $this->applicable = array_diff($delegates, $blacklist);
        }

        if (empty($blacklist)) {
            return $this->applicable = $whitelist;
        }

        // Well, seems neither of the lists is empty, so time for a little magic.
        return $this->applicable = $this->prioritizeBlacklist
            ? array_diff($whitelist, $blacklist)
            : array_intersect($whitelist, $blacklist);
    }

    /**
     * Removes all Delegates from the stack.
     *
     * @return  $this
     */
    public function flush()
    {
        $this->delegates = [];
        $this->applicable = null;

        return $this;
    }

    /**
     * Sets whether the blacklist overrides the whitelist. Ie. when a given Delegate name is present in both arrays
     * it will be treated as blacklisted.
     *
     * @param   bool    $bool   True to have the blacklist override the whitelist, false otherwise.
     */
    public function setPrioritizeBlacklist($bool)
    {
        $this->prioritizeBlacklist = (bool) $bool;
    }

    /**
     * Checks whether the blacklist overrides the whitelist. Ie. when a given Delegate name is present in both
     * arrays it will be treated as blacklisted.
     *
     * @return  bool    True when the blacklist overrides the whitelist, false otherwise.
     */
    public function doesPrioritizeBlacklist()
    {
        return $this->prioritizeBlacklist;
    }

    /**
     * Returns the highest priority currently assigned to any Delegate/callable.
     *
     * Could be used to provide sane priorities when adding Delegates, if you want to ensure that at the given
     * runtime moment the given Delegate takes top-priority but assume that something even more important might
     * get registered and therefore don't want to use some ridiculously high int like max_int.
     *
     * @return  int
     */
    public function getHighestPriority()
    {
        return $this->highestPriority;
    }

    /**
     * Sorts the given Delegates by their priority, highest first.
     *
     * @param   array   &$delegates  The Delegates to sort.
     * @return  $this
     */
    protected function sort(array &$delegates) : self
    {
        uasort($delegates, function($a, $b) {
            return $b['priority'] - $a['priority'];
        });

        return $this;
    }

    /**
     * Pushes the given values to either the black-or-white-list.
     *
     * @param   array           &$list      Either $this->blacklist or $this->whitelist.
     * @param   string|array    $name       {@see self::blacklist()} or {@see self::whitelist()}
     * @return  $this
     */
    protected function pushToList(array &$list, $name) : self
    {
        // Handle arrays (recursively).
        if (is_array($name)) {
            foreach ($name as $single) {
                $this->pushToList($list, $single);
            }
            return $this;
        }

        $list[] = $name;

        // Reset the applicable Delegates as we will need to recompile the list.
        $this->applicable = null;

        return $this;
    }

    /**
     * Compiles the given black-or-white-list based on the given Delegate names, ie. run regex matches against
     * the Delegate names.
     *
     * @param   array   &$list      Either $this->blacklist or $this->whitelist.
     * @param   array   $delegates  An array of Delegate names that should be considered.
     * @return  array               The compiled List.
     */
    protected function compileList(array &$list, array $delegates)
    {
        $return = [];

        foreach ($list as $name) {
            $return[] = preg_grep('/'.$name.'/', $delegates);
        }

        return call_user_func_array('array_merge', $return);
    }

    /**
     * Returns an Inspector instance for the given exception. Kept separately from self::handle() in case you
     * intend to use a custom Inspector.
     *
     * @param   \Throwable      $throwable  The Throwable which is to be inspected.
     * @return  debug\Inspector             A Inspector instance.
     */
    protected function inspect(\Throwable $throwable) : debug\Inspector
    {
        return new debug\Inspector($throwable, $this);
    }

    /**
     * Handles a Delegate's response before the handle() method resolves the return signal. Allows to override
     * the signal by the Handler or act upon responses that are not signals.
     *
     * You might use this method to, for example, intercept delegate responses, generate a HTTP Response from
     * them and stop further delegation by returning a STOP signal yourself.
     *
     * @param   mixed               $response   The response of the last Delegate.
     * @param   debug\Inspector     $inspector  The Inspector handling the Throwable.
     * @return  mixed
     */
    protected function handleDelegateResponse($response, debug\Inspector $inspector)
    {
        return $response;
    }
}
