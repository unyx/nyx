<?php namespace nyx\auth\id\identities;

// Internal dependencies
use nyx\auth\id\protocols\oauth2;

/**
 * Slack Identity
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class Slack extends oauth2\Identity
{
    /**
     * {@inheritDoc}
     */
    protected static $provider = oauth2\providers\Slack::class;

    /**
     * @var string  The identifier of the Team this Identity belongs to (and which provides its access context).
     */
    protected $teamId;

    /**
     * @var string  The name of the Team this Identity belongs to (and which provides its access context).
     */
    protected $teamName;

    /**
     * {@inheritDoc}
     */
    public function __construct(oauth2\Token $token, array $data)
    {
        $this->id     = $data['user']['id']         ?? null;
        $this->email  = $data['user']['email']      ?? '';
        $this->name   = $data['user']['name']       ?? '';
        $this->avatar = $data['user']['image_192']  ?? '';

        $this->teamId   = $data['team']['id']       ?? '';
        $this->teamName = $data['team']['name']     ?? '';

        parent::__construct($token, $data);
    }
}
