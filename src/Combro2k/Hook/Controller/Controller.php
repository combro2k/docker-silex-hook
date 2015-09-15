<?php

namespace Combro2k\Hook\Controller;

use Combro2k\Hook\Application;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Controller
 * @package Combro2k\Hook\Controller
 */
class Controller
{
    /**
     * @var Application
     */
    private $app;

    /**
     * Controller constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->setApp($app);
    }

    /**
     * @param Application $app
     *
     * @return Controller
     */
    protected function setApp($app)
    {
        $this->app = $app;

        return $this;
    }

    /**
     * @return Application
     */
    protected function getApp()
    {
        return $this->app;
    }

    /**
     * @return Response
     */
    public function indexAction()
    {
        return new Response('Nothing here!');
    }

    /**
     * @param Request $request
     *
     * @return Response|void
     */
    public function beforeJsonFilter(Request $request)
    {
        return $this->getApp()->beforeJsonFilter($request);
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return JsonResponse|void
     */
    public function afterJsonFilter(Request $request, Response $response)
    {
        return $this->getApp()->afterJsonFilter($request, $response);
    }

    /**
     * Adds a log record.
     *
     * @param string $message The log message
     * @param array  $context The log context
     * @param int    $level   The logging level
     *
     * @return bool Whether the record has been processed
     */
    public function log($message, array $context = array(), $level = Logger::INFO)
    {
        return $this->getApp()->log($message, $context, $level);
    }

    /**
     * @return \ArrayObject
     */
    public function getConfig()
    {
        return $this->getApp()->getConfig();
    }

    /**
     * @param      $value
     * @param null $default
     *
     * @return mixed
     */
    public function get($value, $default = null)
    {
        return ($config = $this->getConfig()) && $config->offsetExists($value) ? $config->offsetGet($value) : $default;
    }
}