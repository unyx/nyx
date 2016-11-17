<?php namespace nyx\notify\transports\slack\message;

// External dependencies
use nyx\core;
use nyx\diagnostics;

/**
 * Slack Message Attachment
 *
 * See {@see https://api.slack.com/docs/message-attachments} and
 * {@see https://api.slack.com/docs/message-buttons#attachment_fields} for Slack's documentation on using
 * Messages with Attachments.
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 * @todo        PHP7.1 pass for nullable return types (missing ?string on most methods).
 */
class Attachment implements core\interfaces\Serializable
{
    /**
     * The traits of an Attachment.
     */
    use core\traits\Serializable;

    /**
     * The colors an Attachment can be styled with. Note: Those only represent the colors predefined by Slack.
     * Otherwise, the Attachment::$color property accepts any hex color string.
     */
    const COLOR_GOOD    = 'good';
    const COLOR_WARNING = 'warning';
    const COLOR_DANGER  = 'danger';

    /**
     * @var string  Required. A plaintext message displayed to users using a client/interface that does not
     *              support attachments or interactive messages. Consider leaving a URL pointing to your
     *              service if the potential message actions are representable outside of Slack.
     *              Otherwise, let folks know what they are missing.
     */
    protected $fallback;

    /**
     * @var string  Optional. The text that appears above the attachment block.
     */
    protected $pretext;

    /**
     * @var string  Optional. The text that appears within the attachment.
     */
    protected $text;

    /**
     * @var string  Optional. The title of the Attachment.
     */
    protected $title;

    /**
     * @var string  Optional. The URL the title should link to.
     */
    protected $title_link;

    /**
     * @var string  Optional. The image that should appear within the attachment.
     */
    protected $image_url;

    /**
     * @var string  Optional. The thumbnail that should appear within the attachment.
     */
    protected $thumb_url;

    /**
     * @var string  Optional. The name of the author.
     */
    protected $author_name;

    /**
     * @var string  Optional. The URL the author's name should link to.
     */
    protected $author_link;

    /**
     * @var string  Optional. The author's icon.
     */
    protected $author_icon;

    /**
     * @var string  Optional. The color used for the border along the left side of the Attachment.
     */
    protected $color;

    /**
     * @var string  Optional. The text to display in the footer of the Attachment.
     */
    protected $footer;

    /**
     * @var string  Optional. The icon to display in the footer of the Attachment.
     */
    protected $footer_icon;

    /**
     * @var int     Optional. The (Unix) timestamp of the Attachment.
     */
    protected $ts;

    /**
     * @var array   Optional. The attributes which should be parsed by Slack as its Markdown flavour.
     */
    protected $mrkdwn_in = [];

    /**
     * @var attachment\Field[]  The Fields of the Attachment.
     */
    protected $fields = [];

    /**
     * @var string  Optional. The ID of the callback to use for the attached Actions, if any. Is required
     *              When those Actions are present.
     */
    protected $callbackId;

    /**
     * @var attachment\Action[] The Actions of the Attachment. 5 at most.
     */
    protected $actions = [];

    /**
     * Creates a new Attachment instance.
     *
     * @param   array   $attributes
     */
    public function __construct(array $attributes = null)
    {
        if (!empty($attributes)) {
            $this->setAttributes($attributes);
        }
    }

    /**
     * Sets the attributes of this Attachment.
     *
     * @param   array   $attributes
     * @return  $this
     */
    public function setAttributes(array $attributes) : Attachment
    {
        if (isset($attributes['fallback'])) {
            $this->setFallback($attributes['fallback']);
        }

        if (isset($attributes['pretext'])) {
            $this->setPretext($attributes['pretext']);
        }

        if (isset($attributes['text'])) {
            $this->setText($attributes['text']);
        }

        if (isset($attributes['title'])) {
            $this->setTitle($attributes['title']);
        }

        if (isset($attributes['title_link'])) {
            $this->setTitleLink($attributes['title_link']);
        }

        if (isset($attributes['image_url'])) {
            $this->setImageUrl($attributes['image_url']);
        }

        if (isset($attributes['thumb_url'])) {
            $this->setThumbUrl($attributes['thumb_url']);
        }

        if (isset($attributes['author_name'])) {
            $this->setAuthorName($attributes['author_name']);
        }

        if (isset($attributes['author_link'])) {
            $this->setAuthorLink($attributes['author_link']);
        }

        if (isset($attributes['author_icon'])) {
            $this->setAuthorIcon($attributes['author_icon']);
        }

        if (isset($attributes['color'])) {
            $this->setColor($attributes['color']);
        }

        if (isset($attributes['footer'])) {
            $this->setFooter($attributes['footer']);
        }

        if (isset($attributes['footer_icon'])) {
            $this->setFooterIcon($attributes['footer_icon']);
        }

        if (isset($attributes['timestamp'])) {
            $this->setTimestamp($attributes['timestamp']);
        }

        if (isset($attributes['mrkdwn_in'])) {
            $this->setMarkdownAttributes($attributes['mrkdwn_in']);
        }

        if (isset($attributes['fields'])) {
            $this->setFields($attributes['fields']);
        }

        if (isset($attributes['callback_id'])) {
            $this->setCallbackId($attributes['callback_id']);
        }

        if (isset($attributes['actions'])) {
            $this->setActions($attributes['actions']);
        }

        return $this;
    }

    /**
     * Returns the fallback text.
     *
     * @return  string
     */
    public function getFallback()
    {
        return $this->fallback;
    }

    /**
     * Sets the fallback text.
     *
     * @param   string  $fallback
     * @return  $this
     */
    public function setFallback(string $fallback) : Attachment
    {
        $this->fallback = $fallback;

        return $this;
    }

    /**
     * Returns the text that appears above the attachment block.
     *
     * @return  string
     */
    public function getPretext()
    {
        return $this->pretext;
    }

    /**
     * Sets the text that appears above the attachment block.
     *
     * @param   string  $pretext
     * @return  $this
     */
    public function setPretext(string $pretext) : Attachment
    {
        $this->pretext = $pretext;

        return $this;
    }

    /**
     * Returns the text that appears within the attachment.
     *
     * @return  string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Sets the text that appears within the attachment.
     *
     * @param   string  $text
     * @return  $this
     */
    public function setText(string $text) : Attachment
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Returns the title of the Attachment.
     *
     * @return  string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title of the Attachment.
     *
     * @param   string  $title
     * @return  $this
     */
    public function setTitle(string $title) : Attachment
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Returns the URL the title should link to.
     *
     * @return  string
     */
    public function getTitleLink()
    {
        return $this->title_link;
    }

    /**
     * Sets the URL the title should link to.
     *
     * @param   string  $link
     * @return  $this
     */
    public function setTitleLink(string $link) : Attachment
    {
        $this->title_link = $link;

        return $this;
    }

    /**
     * Returns the image that should appear within the attachment.
     *
     * @return  string
     */
    public function getImageUrl()
    {
        return $this->image_url;
    }

    /**
     * Sets the image that should appear within the attachment.
     *
     * @param   string  $url
     * @return  $this
     */
    public function setImageUrl(string $url) : Attachment
    {
        $this->image_url = $url;

        return $this;
    }

    /**
     * Returns the thumbnail that should appear within the attachment.
     *
     * @return  string
     */
    public function getThumbUrl()
    {
        return $this->thumb_url;
    }

    /**
     * Sets the thumbnail that should appear within the attachment.
     *
     * @param   string  $url
     * @return  $this
     */
    public function setThumbUrl(string $url) : Attachment
    {
        $this->thumb_url = $url;

        return $this;
    }

    /**
     * Returns the name of the author.
     *
     * @return  string
     */
    public function getAuthorName()
    {
        return $this->author_name;
    }

    /**
     * Sets the name of the author.
     *
     * @param   string  $author_name
     * @return  $this
     */
    public function setAuthorName(string $author_name) : Attachment
    {
        $this->author_name = $author_name;

        return $this;
    }

    /**
     * Returns the URL the author's name should link to.
     *
     * @return string
     */
    public function getAuthorLink()
    {
        return $this->author_link;
    }

    /**
     * Sets the URL the author's name should link to.
     *
     * @param   string  $url
     * @return  $this
     */
    public function setAuthorLink(string $url) : Attachment
    {
        $this->author_link = $url;

        return $this;
    }

    /**
     * Returns the author's icon.
     *
     * @return string
     */
    public function getAuthorIcon()
    {
        return $this->author_icon;
    }

    /**
     * Sets the author's icon.
     *
     * @param   string  $url
     * @return  $this
     */
    public function setAuthorIcon(string $url) : Attachment
    {
        $this->author_icon = $url;

        return $this;
    }

    /**
     * Returns the color used for the border along the left side of the Attachment.
     *
     * @return  string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Sets the color used for the border along the left side of the Attachment.
     *
     * @param   string  $color  One of the COLOR_* class constants or a hex color code.
     * @return  $this
     */
    public function setColor(string $color) : Attachment
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Returns the text to display in the footer of the Attachment.
     *
     * @return  string
     */
    public function getFooter()
    {
        return $this->footer;
    }

    /**
     * Sets the text to display in the footer of the Attachment.
     *
     * @param   string  $footer
     * @return  $this
     */
    public function setFooter(string $footer) : Attachment
    {
        $this->footer = $footer;

        return $this;
    }

    /**
     * Returns the icon to display in the footer of the Attachment.
     *
     * @return  string
     */
    public function getFooterIcon()
    {
        return $this->footer_icon;
    }

    /**
     * Sets the icon to display in the footer of the Attachment.
     *
     * @param   string  $url
     * @return  $this
     */
    public function setFooterIcon(string $url) : Attachment
    {
        $this->footer_icon = $url;

        return $this;
    }

    /**
     * Returns the (UNIX) timestamp of the Attachment.
     *
     * @return  int
     */
    public function getTimestamp()
    {
        return $this->ts;
    }

    /**
     * Sets the (UNIX) timestamp of the Attachment.
     *
     * @param   int     $time
     * @return  $this
     */
    public function setTimestamp(int $time) : Attachment
    {
        $this->ts = $time;

        return $this;
    }

    /**
     * Returns the attributes which should be parsed by Slack as its Markdown flavour.
     *
     * @return  array
     */
    public function getMarkdownAttributes() : array
    {
        return $this->mrkdwn_in;
    }

    /**
     * Sets the attributes which should be parsed by Slack as its Markdown flavour.
     *
     * @param   array   $attributes
     * @return  $this
     */
    public function setMarkdownAttributes(array $attributes) : Attachment
    {
        $this->mrkdwn_in = $attributes;

        return $this;
    }

    /**
     * Returns the Fields of the Attachment.
     *
     * @return  attachment\Field[]
     */
    public function getFields() : array
    {
        return $this->fields;
    }

    /**
     * Sets the Fields of the Attachment.
     *
     * @param   attachment\Field[] $fields
     * @return  $this
     */
    public function setFields(array $fields) : Attachment
    {
        $this->fields = [];

        foreach ($fields as $title => $value) {

            if (is_string($value)) {
                $this->addField($title, $value);
            } else {
                $this->addField($value);
            }
        }

        return $this;
    }

    /**
     * Adds a Field to the Attachment. Acts as a factory method.
     *
     * @param   mixed   $field
     * @param   string  $value  If given, then $field is treated as the title of the Field and must be a string.
     * @return  $this
     * @throws  \InvalidArgumentException
     */
    public function addField($field, string $value = null) : Attachment
    {
        if (null !== $value && is_string($field)) {
            $this->fields[] = new attachment\Field([
                'title' => $field,
                'value' => $value
            ]);

            return $this;
        }

        if (is_array($field)) {
            $this->fields[] = new attachment\Field($field);

            return $this;
        }

        if ($field instanceof attachment\Field) {
            $this->fields[] = $field;

            return $this;
        }

        throw new \InvalidArgumentException("Expected an array or an instance of ".attachment\Field::class.", got [".diagnostics\Debug::getTypeName($field)."] instead.");
    }

    /**
     * Returns the callback id for the Actions of this Attachment.
     *
     * @return  string
     */
    public function getCallbackId()
    {
        return $this->callbackId;
    }

    /**
     * Sets the callback id for the Actions of this Attachment.
     *
     * @param   string  $id
     * @return  $this
     */
    public function setCallbackId(string $id) : Attachment
    {
        $this->callbackId = $id;

        return $this;
    }

    /**
     * Returns the Actions of the Attachment.
     *
     * @return  attachment\Action[]
     */
    public function getActions() : array
    {
        return $this->actions;
    }

    /**
     * Sets the Actions of the Attachment.
     *
     * @param   array   $actions
     * @return  $this
     */
    public function setActions(array $actions) : Attachment
    {
        $this->actions = [];

        foreach ($actions as $action) {
            $this->addAction($action);
        }

        return $this;
    }

    /**
     * Adds an Action to the Attachment.
     *
     * @param   array|attachment\Action $action
     * @return  $this
     * @throws  \InvalidArgumentException
     */
    public function addAction($action) : Attachment
    {
        if (is_array($action)) {
            $this->actions[] = new attachment\Action($action);

            return $this;
        }

        if ($action instanceof attachment\Action) {
            $this->actions[] = $action;

            return $this;
        }

        throw new \InvalidArgumentException("Expected an array or an instance of ".attachment\Action::class.", got [".diagnostics\Debug::getTypeName($action)."] instead.");
   }

    /**
     * {@inheritDoc}
     */
    public function unserialize($data)
    {
        $this->setAttributes(unserialize($data));
    }

    /**
     * {@inheritDoc}
     */
    public function toArray() : array
    {
        $data = [
            'fallback'    => $this->getFallback(),
            'pretext'     => $this->getPretext(),
            'text'        => $this->getText(),
            'title'       => $this->getTitle(),
            'title_link'  => $this->getTitleLink(),
            'image_url'   => $this->getImageUrl(),
            'thumb_url'   => $this->getThumbUrl(),
            'color'       => $this->getColor(),
            'author_name' => $this->getAuthorName(),
            'author_link' => $this->getAuthorLink(),
            'author_icon' => $this->getAuthorIcon(),
            'footer'      => $this->getFooter(),
            'footer_icon' => $this->getFooterIcon(),
            'ts'          => $this->getTimestamp(),
            'mrkdwn_in'   => $this->getMarkdownAttributes(),
            'callback_id' => $this->getCallbackId(),

            // Empty, we'll populate those two in a second with the toArray'ed values of the objects instead.
            'fields'      => [],
            'actions'     => []
        ];

        foreach ($this->getFields() as $field) {
            $data['fields'][] = $field->toArray();
        }

        foreach ($this->getActions() as $action) {
            $data['actions'][] = $action->toArray();
        }

        return $data;
    }
}
