<?php namespace nyx\notify\transports\mail;

/**
 * Mail Message
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Message extends \Swift_Message
{
    /**
     * @var array   The Message's views.
     */
    protected $views = [];

    /**
     * @var array   The data to pass to the Message's views.
     */
    protected $viewData = [];

    /**
     * Creates a new Mail Message instance.
     *
     * Note: Explicitly not calling (direct) parent constructor.
     *
     * @param   array   $views      The Message's views.
     * @param   string  $subject    The subject of the Message.
     * @param   string  $charset    The character set of the Message (defaults to UTF-8).
     */
    public function __construct(array $views = null, string $subject = null, string $charset = 'UTF-8')
    {
        call_user_func_array(
            [$this, 'Swift_Mime_SimpleMessage::__construct'],
            \Swift_DependencyContainer::getInstance()->createDependenciesFor('mime.message')
        );

        if (isset($views)) {
            $this->setViews($views);
        }

        if (isset($subject)) {
            $this->setSubject($subject);
        }

        $this->setCharset($charset);
    }

    /**
     * Returns the Message's views.
     *
     * @return  array
     */
    public function getViews() : array
    {
        return $this->views;
    }

    /**
     * Sets the Message's views.
     *
     * @param   array   $views  The names of the views to associate.
     * @return  $this
     */
    public function setViews(array $views) : Message
    {
        $this->views = $views;

        return $this;
    }

    /**
     * Returns the data to pass to the Message's views.
     *
     * @return  array
     */
    public function getViewData() : array
    {
        // Always include the Message itself as the 'message' key, unless explicitly overwritten.
        return $this->viewData + ['message' => $this];
    }

    /**
     * Sets the data to pass to the Message's views.
     *
     * @param   mixed   $key    The key the value will be available under or an array of key -> value pairs
     *                          when associating multiple values.
     * @param   mixed   $value  The value to set the specified key as (ignored if the key is an array).
     * @return  $this
     */
    public function with($key, $value = null)
    {
        if (is_array($key)) {
            $this->viewData = array_merge($this->viewData, $key);
        } else {
            $this->viewData[$key] = $value;
        }

        return $this;
    }
}
