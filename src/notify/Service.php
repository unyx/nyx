<?php namespace nyx\notify;

// External dependencies
use Illuminate\Contracts\Foundation\Application;

/**
 * Notify Service Provider
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 * @todo        Make 'mailer' subservice registration optional? Drop the "mailer" name for the FQN of the interface?
 * @todo        Queue support for the Mailer?
 */
class Service extends \Illuminate\Support\ServiceProvider
{
    /**
     * {@inheritDoc}
     */
    public function register()
    {
        $this->app->singleton(TransportManager::class, function (Application $app) {
            return new TransportManager($app);
        });

        $this->app->alias(TransportManager::class, interfaces\Dispatcher::class);

        // Mailer (currently - see the to-do) gets made available as a standalone subservice. Subject to change.
        $this->registerMailer();
    }

    /**
     * Registers the Mail subcomponent as a standalone service for situations where Messages may need to get sent
     * bypassing the way Notifications are otherwise handled.
     */
    protected function registerMailer()
    {
        $this->app->singleton('mailer', function (Application $app) {

            $mailer = new transports\mail\Mailer($app->make('mailer.transport'), $app->make('view'));

            // Set the 'always from' and 'always to' options on the Mailer if they have been configured.
            $config = $app->make('config')->get('mail');

            if (isset($config['from'])) {
                $mailer->setAlwaysFrom($config['from']);
            }

            if (isset($config['to'])) {
                $mailer->setAlwaysTo($config['to']);
            }

            return $mailer;
        });

        // Make the humanized name an alias for the Mailer Interface's FQN so both can be used interchangeably.
        $this->app->alias('mailer', transports\mail\interfaces\Mailer::class);

        // We are going to need to instantiate the Mailer with a proper Driver.
        // @todo Drop the DriverManager in favour of handling the logic in this very Service Provider itself?
        $this->app->bind('mailer.transport', $this->app->share(function (Application $app) {
            return (new transports\mail\DriverManager($app))->driver();
        }));
    }
}
