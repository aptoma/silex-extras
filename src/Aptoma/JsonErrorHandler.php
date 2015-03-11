<?php

namespace Aptoma;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * JsonErrorHandler is able to capture exceptions and do smart stuff with them.
 *
 * Unless you set `$handleNonJsonRequests` to true in the constructor, only requests
 * with an `Accept: application/json` header will be handled.
 *
 * @author Gunnar Lium <gunnar@aptoma.com>
 */
class JsonErrorHandler
{
    /** @var Application */
    private $app;

    /** @var Request */
    private $request;

    /** @var bool */
    private $handleNonJsonRequests;

    public function __construct(Application $app, $handleNonJsonRequests = false)
    {
        $this->app = $app;
        $this->handleNonJsonRequests = $handleNonJsonRequests;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    public function handle(\Exception $e, $code)
    {
        if (!$this->request) {
            try {
                $this->request = $this->app['request'];
            } catch (\RuntimeException $e) {
                return null;
            }
        }

        if (!$this->shouldHandleRequest()) {
            return null;
        }

        if ($e instanceof HttpException) {
            return $this->handleHttpException($e, $code);
        }

        return $this->handleGenericException($e, $code);
    }

    private function handleHttpException(HttpException $e, $code)
    {
        $message = array(
            'status' => $e->getStatusCode(),
            'code' => $code,
            'message' => $e->getMessage()
        );

        return $this->app->json(
            $message,
            $e->getStatusCode(),
            $e->getHeaders()
        );
    }

    private function handleGenericException(\Exception $e, $code)
    {
        $message = array(
            'status' => 500,
            'code' => $code,
            'message' => $e->getMessage()
        );

        return $this->app->json(
            $message,
            500,
            array('Content-Type' => 'application/json')
        );
    }

    /**
     * @return bool
     */
    private function shouldHandleRequest()
    {
        if ($this->handleNonJsonRequests) {
            return true;
        }

        return in_array('application/json', $this->request->getAcceptableContentTypes());
    }
}
