<?php namespace nyx\notify\transports\mail;

/**
 * Mailer
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Mailer implements interfaces\Mailer
{
    /**
     * @var interfaces\Driver   The Driver used to actually send Messages.
     */
    protected $driver;

    /**
     * @var \Illuminate\Contracts\View\Factory  The View Factory responsible for creating the requested views.
     */
    protected $viewFactory;

    /**
     * @var array   An array of (optional) 'always to' and 'always from' addresses to simplify the creation and testing
     *              of Messages.
     */
    protected $always = [];

    /**
     * @var int     The maximum number of retries allowed to make upon driver connection failures.
     */
    protected $allowedRetries = 5;

    /**
     * @var int     The current number of connection retries made.
     */
    protected $currentRetries = 0;

    /**
     * Constructs a new Mailer instance.
     *
     * @param   interfaces\Driver                   $driver         The Driver to use to actually send Messages.
     * @param   \Illuminate\Contracts\View\Factory  $viewFactory    The View Factory responsible for creating the requested views.
     */
    public function __construct(interfaces\Driver $driver, \Illuminate\Contracts\View\Factory $viewFactory)
    {
        $this->driver      = $driver;
        $this->viewFactory = $viewFactory;
    }

    /**
     * Sets the "always from" message header, which is used to populate the "from" field on Messages that do not yet
     * have a sender set.
     *
     * @param   string|array    $address
     * @return  $this
     */
    public function setAlwaysFrom($address) : Mailer
    {
        $this->always['from'] = $address;

        return $this;
    }

    /**
     * Sets the "always to" message header, which is used to populate the "to" field on *all* outgoing Messages,
     * regardless if they already have any recipients set.
     *
     * This functionality is primarily intended for testing purposes.
     *
     * @param   string|array    $address
     * @return  $this
     */
    public function setAlwaysTo($address)
    {
        $this->always['to'] = $address;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function send($view, array $data = null, callable $builder = null, array &$failures = null) : int
    {
        if ($view instanceof Message) {
            $message = $view;
        } else {
            $message = $this->createMessage($view);
        }

        // Pass the view data to the Message, if it was given.
        if (isset($data)) {
            $message->with($data);
        }

        // Render the views and associate the resulting output as the body and parts of the outgoing Message.
        $this->buildEntities($message);

        // Set the "always from" header field but only if no sender is already set. Opposed to "always to",
        // the "always from" field does not override existing headers.
        if (isset($this->always['from']) && empty($message->getFrom())) {
            $message->setFrom($this->always['from']);
        }

        if (isset($builder)) {
            return call_user_func($builder, $message);
        }

        // The "always to" header field overrides any recipients set on outgoing Messages, if it's configured.
        if (isset($this->always['to'])) {
            $message->setTo($this->always['to']);
        }

        return $this->doSend($message, $failures);
    }

    /**
     * Performs the actual sending of a MIME Message using the configured Driver.
     *
     * @param   \Swift_Mime_Message $message    The Message to send.
     * @param   array               &$failures  A reference to an array which will hold data about all requested
     *                                          recipients sending to whom failed.
     * @return  int                             The number of recipients the Message has been sent to.
     */
    protected function doSend(\Swift_Mime_Message $message, array &$failures = null) : int
    {
        try {

            if ($this->driver instanceof interfaces\drivers\Process && !$this->driver->isStarted()) {
                $this->driver->start();
            }

            $count = $this->driver->send($message, $failures);

            // Reset the retry counter on successful requests.
            $this->currentRetries = 0;

            return $count;

        } catch (\Swift_RfcComplianceException $exception) {

            if (isset($failures)) {
                foreach ($message->getTo() as $address => $name) {
                    $failures[] = $address;
                }
            }

        } catch (\Swift_TransportException $exception) {
            return $this->handleTransportException($message, $exception, $failures);
        }
    }

    /**
     * Attempts to recover from a Driver Exception by retrying delivery, restarting the Driver's Process if need be,
     * unless the maximum number of allowed retries has been exceeded.
     *
     * @param   \Swift_Mime_Message         $message    The Message we are trying to send.
     * @param   \Swift_TransportException   $exception  The Exception we are attempting to recover from.
     * @param   array                       &$failures  A reference to an array which will hold data about all requested
     *                                                  recipients sending to whom failed.
     * @return  int                                     The number of recipients the Message has been sent to.
     * @throws  \RuntimeException                       When recovery was impossible due to exceeding the maximum number
     *                                                  of allowed retries.
     */
    protected function handleTransportException(\Swift_Mime_Message $message, \Swift_TransportException $exception, array &$failures = null) : int
    {
        // Prevent further re-tries if we're at or above our threshold.
        if ($this->currentRetries >= $this->allowedRetries) {
            throw new \RuntimeException('The mail transport connection failed. Retried connecting '.$this->currentRetries.' time(s).');
        }

        // Transport exceptions in case of Process drivers may just be temporary, so we'll try to re-establish
        // the process (connection) from scratch in those cases.
        if ($this->driver instanceof interfaces\drivers\Process) {
            $this->driver->stop();
        }

        $this->currentRetries++;

        // Try again.
        return $this->doSend($message, $failures);
    }

    /**
     * Renders a Message's views and associates the resulting output as the body
     * and respective MIME parts of the Message.
     *
     * @param   Message $message    The Message whose MIME entities should be set.
     */
    protected function buildEntities(Message $message)
    {
        list($view, $plain, $raw) = $this->determineViews($message->getViews());

        $data = $message->getViewData();

        if (isset($view)) {
            $message->setBody($this->viewFactory->make($view, $data)->render(), 'text/html');
        }

        if (isset($plain)) {
            $method = isset($view) ? 'addPart' : 'setBody';

            $message->$method($this->viewFactory->make($plain, $data)->render(), 'text/plain');
        }

        if (isset($raw)) {
            $method = (isset($view) || isset($plain)) ? 'addPart' : 'setBody';

            $message->$method($raw, 'text/plain');
        }
    }

    /**
     * Determines what type of views (html, text or raw) are specified in the given $view value.
     *
     * @param   string|array    $view       The value to base on.
     * @return  array                       A numerically indexed array with the respective view names corresponding to:
     *                                      0 => html, 1 => text, 2 => raw view names.
     * @throws  \InvalidArgumentException   If the given value is neither a string nor an array.
     */
    protected function determineViews($view) : array
    {
        if (is_string($view)) {
            return [$view];
        }

        if (is_array($view)) {

            if (isset($view[0])) {
                return [$view[0], $view[1]];
            }

            return [
                $view['html'] ?? null,
                $view['text'] ?? null,
                $view['raw']  ?? null,
            ];
        }

        throw new \InvalidArgumentException('Unrecognized view format given - unable to determine which views to render.');
    }

    /**
     * Creates a new Mail Message.
     *
     * @param   string|array    $view   The view(s) to associate the Message with.
     * @return  Message
     */
    protected function createMessage($view) : Message
    {
        return new Message((array) $view);
    }
}
