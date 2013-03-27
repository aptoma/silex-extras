<?php
namespace Aptoma\Log;

use Silex\Application;

/**
 * RequestProcessor adds extra information about the request.
 *
 * It will add clientIp and a unique request token, as well as the username
 * of the currently logged in user if the Symfony Security Component is in use.
 *
 * @author Gunnar Lium <gunnar@aptoma.com>
 */
class RequestProcessor
{
    /**
     * @var Application
     */
    private $app;
    private $token;
    private $clientIp;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->token = uniqid();
    }

    public function __invoke(array $record)
    {
        $record['extra']['clientIp'] = $this->getClientIp();
        if ($this->app->offsetExists('security')) {
            $record['extra']['user'] = $this->getUsername();
        }
        $record['extra']['token'] = $this->token;

        return $record;
    }

    private function getClientIp()
    {
        if (!$this->clientIp) {
            try {
                $this->clientIp = $this->app['request']->getClientIp();
            } catch (\RuntimeException $e) {
                // Will be reached if we are not in a request context
                // This only happens if we log stuff before starting to handle the request,
                // or if we don't log anything before the `finish` application middleware.
            }
        }

        return $this->clientIp;
    }

    private function getUsername()
    {
        try {
            $token = $this->app['security']->getToken();
            if ($token) {
                return $token->getUsername();
            }
        } catch (\InvalidArgumentException $e) {
        }

        return '';
    }
}
