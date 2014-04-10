<?php
namespace Aptoma\Log;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

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

    public function __construct(Application $app, $token = null)
    {
        $this->app = $app;
        $this->token = $token ?: uniqid();
    }

    public function __invoke(array $record)
    {
        $record['extra']['clientIp'] = $this->getClientIp();
        if ($this->app->offsetExists('security')) {
            $record['extra']['user'] = $this->getUsername();
        }
        $record['extra']['token'] = $this->token;

        /** @var Request $request */
        $request = $this->app['request_stack']->getCurrentRequest();
        if ($request && null !== $remoteRequestToken = $request->headers->get('X-Remote-Request-Token')) {
            $record['extra']['remoteRequestToken'] = $remoteRequestToken;
        }

        return $record;
    }

    private function getClientIp()
    {
        if (!$this->clientIp) {
            if ($this->app['request_stack']->getCurrentRequest()) {
                $this->clientIp = $this->app['request_stack']->getCurrentRequest()->getClientIp();
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
