<?php namespace nyx\connect\http\traits;

// Vendor dependencies
use Psr;

/**
 * Message Trait
 *
 * Allows for the implementation of Psr\Http\Message\MessageInterface. Note: Currently does not provide *any*
 * sort of validation of the headers.
 *
 * @package     Nyx\Connect
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/connect/index.html
 */
trait Message
{
    /**
     * @var array   Cached headers in a key => array of lowercase values format.
     */
    private $headers = [];

    /**
     * @var array   A map of normalized header names to their original names.
     */
    private $headerNames = [];

    /**
     * @var string  The protocol version used by this Message.
     */
    private $protocolVersion = '1.1';

    /**
     * @var Psr\Http\Message\StreamInterface
     */
    private $body;

    /**
     * {@see Psr\Http\Message\StreamInterface::getProtocolVersion()}
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * {@see Psr\Http\Message\StreamInterface::withProtocolVersion()}
     */
    public function withProtocolVersion($version)
    {
        // Avoid cloning if the result would contain the same protocol version.
        if ($this->protocolVersion === $version) {
            return $this;
        }

        $new = clone $this;
        $new->protocolVersion = $version;

        return $new;
    }

    /**
     * {@see Psr\Http\Message\StreamInterface::getHeaders()}
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * {@see Psr\Http\Message\StreamInterface::hasHeader()}
     */
    public function hasHeader($header)
    {
        return isset($this->headerNames[strtolower($header)]);
    }

    /**
     * {@see Psr\Http\Message\StreamInterface::getHeader()}
     */
    public function getHeader($name)
    {
        // We need a normalized header name.
        $name = strtolower($name);

        // Assume that if we've got the normalized name, we've also got the actual header.
        if (isset($this->headerNames[$name])) {
            return $this->headers[$this->headerNames[$name]];
        }

        // Per PSR-7 spec return an empty array if the header is not set.
        return [];
    }

    /**
     * {@see Psr\Http\Message\StreamInterface::getHeaderLine()}
     */
    public function getHeaderLine($name)
    {
        return implode(',', $this->getHeader($name));
    }

    /**
     * {@see Psr\Http\Message\StreamInterface::withHeader()}
     */
    public function withHeader($header, $value)
    {
        // Normalize the header names.
        $header = trim($header);
        $name   = strtolower($header);

        // Create a clone and set the given header in the clone.
        $new = clone $this;

        $new->headerNames[$name] = $header;
        $new->headers[$header]   = (array) $value;

        return $new;
    }

    /**
     * {@see Psr\Http\Message\StreamInterface::withAddedHeader()}
     */
    public function withAddedHeader($header, $value)
    {
        // Follow a different procedure if the given header isn't set yet at all.
        if (!$this->hasHeader($header)) {
            return $this->withHeader($header, $value);
        }

        // Normalize the header name.
        $name   = strtolower($header);
        $header = $this->headerNames[$name];

        // Create a clone and append the value to the specified header's values.
        $new = clone $this;
        $new->headers[$header] = array_merge($this->headers[$header], (array) $value);

        return $new;
    }

    /**
     * {@see Psr\Http\Message\StreamInterface::withoutHeader()}
     */
    public function withoutHeader($header)
    {
        // Don't create a new clone if the given header isn't set at all.
        if (!$this->hasHeader($header)) {
            return $this;
        }

        // Normalize the header name.
        $name     = strtolower($header);
        $original = $this->headerNames[$name];

        // Unset the actual header with its values and the normalized header name in the map.
        $new = clone $this;
        unset($new->headers[$original], $new->headerNames[$name]);

        return $new;
    }

    /**
     * {@see Psr\Http\Message\StreamInterface::getBody()}
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * {@see Psr\Http\Message\StreamInterface::withBody()}
     */
    public function withBody(Psr\Http\Message\StreamInterface $body)
    {
        // Avoid creating a clone if the body is exactly the same. Note that the underlying stream resource
        // may still be exactly the same, but can't check for that as the StreamInterface does not expose it.
        if ($this->body === $body) {
            return $this;
        }

        $new = clone $this;
        $new->body = $body;

        return $new;
    }
}
