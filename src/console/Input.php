<?php namespace nyx\console;

/**
 * Input
 *
 * Base class for concrete Input formats.
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
abstract class Input implements interfaces\Input
{
    /**
     * @var interfaces\input\Tokens             The raw, unmapped and not validated input Tokens.
     */
    protected $raw;

    /**
     * @var input\parameter\values\Arguments    The Input Arguments collection.
     */
    private $arguments;

    /**
     * @var input\parameter\values\Options      The Input Options collection.
     */
    private $options;

    /**
     * Parses the raw Tokens into usable Input Arguments and Options.
     *
     * @return  $this
     */
    abstract protected function parse() : Input;

    /**
     * {@inheritdoc}
     */
    public function bind(input\Definition $definition) : interfaces\Input
    {
        $this->arguments = new input\parameter\values\Arguments($definition->arguments());
        $this->options   = new input\parameter\values\Options($definition->options());

        $this->parse();

        $this->arguments->finalize();
        $this->options->finalize();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function raw() : interfaces\input\Tokens
    {
        return $this->raw;
    }

    /**
     * {@inheritdoc}
     */
    public function arguments() : ?input\parameter\values\Arguments
    {
        return $this->arguments;
    }

    /**
     * {@inheritdoc}
     */
    public function options() : ?input\parameter\values\Options
    {
        return $this->options;
    }

    /**
     * Magic getter.
     *
     * @param   string  $property   The name of the property whose value is being requested.
     * @return  mixed
     * @throws  \DomainException    When the requested property does not exist or is not accessible.
     */
    public function __get(string $property)
    {
        // Limit magic access to a predefined list. Request access through the actual getters.
        // This adds some overhead, but hurr durr console hurr durr magic method, anyways.
        if (in_array($property, ['raw', 'arguments', 'options'])) {
            return $this->$property();
        }

        throw new \DomainException("The property [$property] does not exist or is not accessible.");
    }

    /**
     * Magic isset.
     *
     * @param   string  $property   The name of the property whose existence we are checking.
     * @return  bool                True when the property is set, false otherwise and when it is not accessible.
     */
    public function __isset(string $property) : bool
    {
        // Limit magic access to a predefined list.
        if (in_array($property, ['raw', 'arguments', 'options'])) {
            return isset($this->$property);
        }

        return false;
    }
}
