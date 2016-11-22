<?php namespace nyx\notify\transports\mail;

/**
 * Mail Driver Manager
 *
 * @package     Nyx\Notify
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
class DriverManager extends \Illuminate\Support\Manager
{
    /**
     * Creates a Mail() Mail Driver.
     *
     * @return  drivers\Mail
     */
    protected function createMailDriver() : drivers\Mail
    {
        return new drivers\Mail;
    }

    /**
     * Creates a SMTP Mail Driver.
     *
     * @return  drivers\Smtp
     */
    protected function createSmtpDriver() : drivers\Smtp
    {
        $config = $this->app->make('config')->get('mail.smtp');
        $driver = new drivers\Smtp($config['host'], $config['port'], $config['encryption'] ?? null);

        if (isset($config['username'])) {
            $driver->setUsername($config['username']);
            $driver->setPassword($config['password']);
        }

        if (isset($config['stream'])) {
            $driver->setStreamOptions($config['stream']);
        }

        return $driver;
    }

    /**
     * Creates a Sendmail Mail Driver.
     *
     * @return  drivers\Sendmail
     */
    protected function createSendmailDriver() : drivers\Sendmail
    {
        return new drivers\Sendmail($this->app->make('config')->get('mail.sendmail'));
    }

    /**
     * Creates a Mailgun Mail Driver.
     *
     * @return  drivers\Mailgun
     */
    protected function createMailgunDriver() : drivers\Mailgun
    {
        $config = $this->app->make('config')->get('services.mailgun', []);

        return new drivers\Mailgun($this->createHttpClient($config), $config['secret'], $config['domain']);
    }

    /**
     * Creates a Mandrill Mail Driver.
     *
     * @return  drivers\Mandrill
     */
    protected function createMandrillDriver() : drivers\Mandrill
    {
        $config = $this->app->make('config')->get('services.mandrill', []);

        return new drivers\Mandrill($this->createHttpClient($config), $config['secret']);
    }

    /**
     * Creates a Postmark Mail Driver.
     *
     * @return  drivers\Postmark
     */
    protected function createPostmarkDriver() : drivers\Postmark
    {
        $config = $this->app->make('config')->get('services.postmark', []);

        return new drivers\Postmark($this->createHttpClient($config), $config['secret']);
    }

    /**
     * Creates a SparkPost Mail Driver.
     *
     * @return  drivers\SparkPost
     */
    protected function createSparkPostDriver() : drivers\SparkPost
    {
        $config = $this->app->make('config')->get('services.sparkpost', []);

        return new drivers\SparkPost($this->createHttpClient($config), $config['secret'], $config['options'] ?? []);
    }

    /**
     * Creates a Sendgrid Mail Driver.
     *
     * @return  drivers\Sendgrid
     */
    protected function createSendgridDriver() : drivers\Sendgrid
    {
        $config = $this->app->make('config')->get('services.sendgrid', []);

        return new drivers\Sendgrid($this->createHttpClient($config), $config['secret']);
    }

    /**
     * Creates a Log Mail Driver.
     *
     * @return  drivers\Log
     */
    protected function createLogDriver() : drivers\Log
    {
        return new drivers\Log($this->app->make('Psr\Log\LoggerInterface'));
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultDriver()
    {
        return $this->app->make('config')->get('mail.driver');
    }

    /**
     * Creates a new HTTP Client instance.
     *
     * @param   array   $config                 The Client's configuration options.
     * @return  \GuzzleHttp\ClientInterface     The created HTTP Client.
     */
    protected function createHttpClient(array $config) : \GuzzleHttp\ClientInterface
    {
        $config                    = $config['http']            ?? [];
        $config['connect_timeout'] = $config['connect_timeout'] ?? 60;

        return new \GuzzleHttp\Client($config);
    }
}
