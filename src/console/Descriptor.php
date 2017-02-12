<?php namespace nyx\console;

// External dependencies
use nyx\diagnostics;

// Internal dependencies
use nyx\console\input\parameter\definitions;

/**
 * Descriptor
 *
 * The particular methods are public for simplicity's sake but if you intend to use them directly, ensure you are
 * checking for the type of this class, not for the interface, as the interface does not require them to be present
 * for the contract to be fulfilled.
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
abstract class Descriptor implements interfaces\Descriptor
{
    /**
     * @var array   A map of supported types to their respective instance methods, minus the 'describe' prefix.
     */
    protected $types = [
        Application::class      => 'Application',
        Suite::class            => 'Suite',
        Command::class          => 'Command',
        input\Definition::class => 'InputDefinition',
        input\Argument::class   => 'InputArgument',
        input\Option::class     => 'InputOption',
        input\Value::class      => 'InputValue',
    ];

    /**
     * {@inheritdoc}
     */
    public function describe($object, array $options = null)
    {
        foreach ($this->types as $class => $type) {
            if ($object instanceof $class) {
                return $this->{'describe'.$type}($object, $options);
            }
        }

        throw new \InvalidArgumentException("The type [".diagnostics\Debug::getTypeName($object)."] is not supported by this Descriptor.");
    }

    /**
     * Describes an Application.
     *
     * @param   Application $application    The Application to describe.
     * @param   array       $options        Additional options to be considered by the Descriptor.
     * @return  mixed
     */
    abstract public function describeApplication(Application $application, array $options = null);

    /**
     * Describes a Suite.
     *
     * @param   Suite   $suite      The Suite to describe.
     * @param   array   $options    Additional options to be considered by the Descriptor.
     * @return  mixed
     */
    abstract public function describeSuite(Suite $suite, array $options = null);

    /**
     * Describes a Command.
     *
     * @param   Command $command    The Command to describe.
     * @param   array   $options    Additional options to be considered by the Descriptor.
     * @return  mixed
     */
    abstract public function describeCommand(Command $command, array $options = null);

    /**
     * Describes an Input Definition.
     *
     * @param   input\Definition    $definition The Input Definition to describe.
     * @param   array               $options    Additional options to be considered by the Descriptor.
     * @return  mixed
     */
    abstract public function describeInputDefinition(input\Definition $definition, array $options = null);

    /**
     * Describes an Input Argument.
     *
     * @param   input\Argument  $argument   The Input Argument to describe.
     * @param   array           $options    Additional options to be considered by the Descriptor.
     * @return  mixed
     */
    abstract public function describeInputArgument(input\Argument $argument, array $options = null);

    /**
     * Describes an Input Option.
     *
     * @param   input\Option    $option     The Input Option to describe.
     * @param   array           $options    Additional options to be considered by the Descriptor.
     * @return  mixed
     */
    abstract public function describeInputOption(input\Option $option, array $options = null);

    /**
     * Describes an Input Value.
     *
     * @param   input\Value     $value      The Input Value to describe.
     * @param   array           $options    Additional options to be considered by the Descriptor.
     * @return  mixed
     */
    abstract public function describeInputValue(input\Value $value, array $options = null);

    /**
     * Provides a synopsis (ie. a string describing the usage) for the given Command.
     *
     * Kept in the abstract Descriptor as the generated string is rather generic, but may require overrides for
     * specific formats and therefore should not be kept within the Command class.
     *
     * @param   Command     $command    The Command to provide the synopsis for.
     * @return  mixed
     */
    public function getCommandSynopsis(Command $command, array $options = null)
    {
        $definition     = $command->getDefinition();
        $inputOptions   = $definition->options();
        $inputArguments = $definition->arguments();

        // We are following the docopt specification here. Options are followed by '--', followed by arguments.
        // @link http://docopt.org
        $output = $this->getInputOptionsSynopsis($inputOptions, $options);

        if (!$inputOptions->isEmpty() && !$inputArguments->isEmpty()) {
            $output .= ' [--] ';
        }

        return $output . $this->getInputArgumentsSynopsis($inputArguments, $options);
    }

    /**
     * Provides a synopsis for the given Input Option Definitions.
     *
     * @param   definitions\Options     $definitions    The Input Option Definitions to provide a synopsis for.
     * @param   array                   $options        Additional options to be considered by the Descriptor.
     * @return  mixed
     */
    public function getInputOptionsSynopsis(definitions\Options $definitions, array $options = null)
    {
        if ($definitions->isEmpty()) {
            return '';
        }

        // The 'short_synopsis' option is primarily used by the Help command which lists all Input Options
        // separately from the synopsis for brevity.
        if ($options['short_synopsis'] ?? false) {
            return '[options]';
        }

        $items = [];

        foreach ($definitions as $option) {
            $items[] = $this->getInputOptionSynopsis($option, $options);
        }

        return implode(' ', $items);
    }

    /**
     * Provides a synopsis for the given Input Option.
     *
     * @param   input\Option    $definition     The Input Option to provide a synopsis for.
     * @param   array           $options        Additional options to be considered by the Descriptor.
     * @return  mixed
     */
    public function getInputOptionSynopsis(input\Option $definition, array $options = null)
    {
        $shortcut = $definition->getShortcut() ? sprintf('-%s|', $definition->getShortcut()) : '';

        if ($value = $definition->getValue()) {
            $format = $value->is(input\Value::REQUIRED) ? '%s--%s="..."' : '%s--%s[="..."]';
        } else {
            $format = '%s--%s';
        }

        return sprintf('['.$format.']', $shortcut, $definition->getName());
    }

    /**
     * Provides a synopsis for the given Input Argument Definitions.
     *
     * @param   definitions\Arguments   $definitions    The Input Argument Definitions to provide a synopsis for.
     * @param   array                   $options        Additional options to be considered by the Descriptor.
     * @return  mixed
     */
    public function getInputArgumentsSynopsis(definitions\Arguments $definitions, array $options = null)
    {
        if ($definitions->isEmpty()) {
            return '';
        }

        $items = [];

        /** @var input\Argument $argument */
        foreach ($definitions as $argument) {
            $items[] = $this->getInputArgumentSynopsis($argument, $options);
        }

        return implode(' ', $items);
    }

    /**
     * Provides a synopsis for the given Input Argument.
     *
     * @param   input\Argument  $definition     The Input Argument to provide a synopsis for.
     * @param   array           $options        Additional options to be considered by the Descriptor.
     * @return  mixed
     */
    public function getInputArgumentSynopsis(input\Argument $definition, array $options = null)
    {
        $output = '\<'.$definition->getName().'>';
        $value  = $definition->getValue();

        if (!$value->is(input\Value::REQUIRED)) {
            $output = '['.$output.']';
        } elseif ($value instanceof input\values\Multiple) {
            $output = $output.' ('.$output.')';
        }

        if ($value instanceof input\values\Multiple) {
            $output .= '...';
        }

        return $output;
    }
}
