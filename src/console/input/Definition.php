<?php namespace nyx\console\input;

// Internal dependencies
use nyx\console\input\parameter\definitions;

/**
 * Input Definition
 *
 * This class represents a master Input Definition, eg. one that contains definitions for all parameters
 * allowed to be present in the Input.
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Definition
{
    /**
     * @var definitions\Arguments   The Definitions of the Arguments that can be present in the Input.
     */
    private $arguments;

    /**
     * @var definitions\Options     The Definitions of the Options that can be present in the Input.
     */
    private $options;

    /**
     * Constructs a new Input Definition instance.
     *
     * @param   definitions\Arguments|array     $arguments  The defined Arguments.
     * @param   definitions\Options|array       $options    The defined Options.
     */
    public function __construct($arguments = null, $options = null)
    {
        $this->arguments = $arguments instanceof definitions\Arguments ? $arguments : new definitions\Arguments($arguments);
        $this->options   = $arguments instanceof definitions\Options   ? $options   : new definitions\Options($options);
    }

    /**
     * Returns the Definitions of the Arguments that can be present in the Input.
     *
     * @return  definitions\Arguments
     */
    public function arguments() : definitions\Arguments
    {
        return $this->arguments;
    }

    /**
     * Returns the Definitions of the Options that can be present in the Input.
     *
     * @return  definitions\Options
     */
    public function options() : definitions\Options
    {
        return $this->options;
    }

    /**
     * Merges this Definition with other Definition(s) and returns the result as a new instance, meaning this one,
     * even though used as base for the merger, will be left unscathed.
     *
     * The merge order works just like array_merge(). Since all parameters are named, duplicates will be overwritten.
     * The method creates two new Parameters collections for the merged Arguments and Options. If you use customized bags
     * you will need to override the method, as they are not injected for simplicity's sake.
     *
     * @param   bool|Definition[]   $mergeArguments     Whether to merge the arguments. Can be omitted (ie. you may
     *                                                  pass a Definition as the first argument to this method right
     *                                                  away, in which case the default of "true" will be used).
     * @param   Definition          ...$definitions     The Definitions to merge with this one.
     * @return  Definition                              The merged Definition as a new instance.
     * @throws  \InvalidArgumentException               When one or more of the parameters is not a Definition
     *                                                  instance (not including the $mergeArguments bool).
     */
    public function merge($mergeArguments = true, Definition ...$definitions) : Definition
    {
        // Whether to merge the arguments. When the first argument is a Definition already, we will use the default
        // of true. Otherwise, strip the first argument out of what we assume to be just an array of Definitions
        // after the func_get_args() call.
        if ($mergeArguments instanceof Definition) {
            array_unshift($definitions, $mergeArguments);
            $mergeArguments = true;
        } else {
            $mergeArguments = (bool) $mergeArguments;
        }

        // We are not simply going to merge the arrays. We'll let the collections do their work and report
        // any duplicates etc. as necessary.
        $arguments = clone $this->arguments;
        $options   = clone $this->options;

        foreach ($definitions as $definition) {
            // Arguments are merged by default but don't necessarily have to be (for instance, help descriptions
            // only display the argument for the command itself, not for the whole command chain).
            if ($mergeArguments) {
                foreach ($definition->arguments as $argument) {
                    $arguments->add($argument);
                }
            }

            // Options are always merged.
            foreach ($definition->options as $option) {
                $options->add($option);
            }
        }

        return new static($arguments, $options);
    }
}
