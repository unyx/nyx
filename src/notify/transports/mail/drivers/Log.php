<?php namespace nyx\notify\transports\mail\drivers;

// Internal dependencies
use nyx\notify\transports\mail;

/**
 * Log Mail Driver
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Log implements mail\interfaces\Driver
{
    /**
     * The traits of a Log Mail Driver instance.
     */
    use traits\CountsRecipients;

    /**
     * @var \Psr\Log\LoggerInterface    The Logger used to log Messages to.
     */
    protected $logger;

    /**
     * Creates a new Log Mail Driver instance.
     *
     * @param   \Psr\Log\LoggerInterface    $logger The Logger to log Messages to.
     */
    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function send(\Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $this->logger->debug($this->getMimeEntityString($message));

        return $this->countRecipients($message);
    }

    /**
     * Returns the MIME data of a MIME entity represented as a MIME string. Supports nested entities.
     *
     * @param   \Swift_Mime_MimeEntity  $entity
     * @return  string
     */
    protected function getMimeEntityString(\Swift_Mime_MimeEntity $entity) : string
    {
        $result = (string) $entity->getHeaders() . PHP_EOL . $entity->getBody();

        foreach ($entity->getChildren() as $child) {
            $result .= PHP_EOL . PHP_EOL . $this->getMimeEntityString($child);
        }

        return $result;
    }
}
