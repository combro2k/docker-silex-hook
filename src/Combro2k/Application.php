<?php

namespace Combro2k;

use Combro2k\Hook\Controller;
use Combro2k\Hook\Controller\HooksController;
use Combro2k\Hook\Provider\ConfigServiceProvider;
use Combro2k\Hook\Provider\HooksControllerProvider;
use MarcW\Silex\Provider\BuzzServiceProvider;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use Silex\Application as BaseApplication;
use Silex\ControllerCollection;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Application
 * @package Combro2k\Hook
 */
class Application extends BaseApplication
{
    use BaseApplication\SecurityTrait;
    use BaseApplication\SwiftmailerTrait;
    use BaseApplication\MonologTrait;

    /**
     * @var \ArrayObject
     */
    private $config;

    /**
     * Adds add options which are needed to run the application.
     *
     * After it boots all service providers.
     *
     * This method is automatically called by handle(), but you can use it
     * to boot all service providers when not handling a request.
     */
    public function boot()
    {
        $this->registerProviders();
        $this->setupControllers();
        $this->setupRoutes();

        if ($this->offsetExists('config') && $config = $this->offsetGet('config')) {
            $this->setupLogging($config);
            $this->setupDatabase($config);
        }

        parent::boot();
    }

    /**
     * @param Request $request
     *
     * @return Response|void
     */
    public function beforeJsonFilter(Request $request)
    {
        if ($request->getContentType() === 'json') {
            if (($data = trim($request->getContent())) === '') {
                return new JsonResponse('Empty context!', Response::HTTP_EXPECTATION_FAILED);
            } elseif (!$data = json_decode($data, true)) {
                return new JsonResponse('Can not decode JSON', Response::HTTP_BAD_REQUEST);
            }

            $request->request->replace(is_array($data) ? $data : array());
        }

        return null;
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return JsonResponse|void
     */
    public function afterJsonFilter(Request $request, Response $response)
    {
        if ($response instanceof JsonResponse === false && $request->getContentType() === 'json') {
            return new JsonResponse($response->getContent(), $response->getStatusCode(), $response->headers->all());
        }

        return $response;
    }

    /**
     * @return ControllerCollection
     */
    public function getControllerFactory()
    {
        return $this->offsetGet('controllers_factory');
    }

    /**
     * @return \ArrayObject
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param \ArrayObject $config
     *
     * @return Application
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Register all providers we want to use
     */
    private function registerProviders()
    {
        parent::register(new ConfigServiceProvider(), array('config.path' => sprintf('%s/%s', $this['rootPath'], 'config/config.yml')));
        parent::register(new MonologServiceProvider(), array(
            'monolog.name'    => 'Hook',
            'monolog.handler' => new ConsoleHandler(new ConsoleOutput(ConsoleOutput::VERBOSITY_VERY_VERBOSE)),
        ));
        parent::register(new BuzzServiceProvider());
        parent::register(new ServiceControllerServiceProvider());
        parent::register(new DoctrineServiceProvider());
    }

    /**
     * Setup all route paths we want to use
     */
    private function setupControllers()
    {
        parent::offsetSet('controller', parent::share(function ($app) {
            return new Controller($app);
        }));

        parent::offsetSet('hooks.controller', parent::share(function ($app) {
            return new HooksController($app);
        }));

        parent::error(function (\Exception $e) {
            $this->log($e->getMessage(), $e->getTrace(), Logger::ERROR);

            return $this['controller']->indexAction();
        });

        Request::setTrustedProxies(array('127.0.0.1', '192.168.1.0/24', '172.17.41.0/16'));
    }

    /**
     * Setup routes
     */
    private function setupRoutes()
    {
        /** Hooks route */
        parent::mount('/hooks', new HooksControllerProvider());
    }

    /**
     * Setup Logging
     *
     * @param \ArrayObject $config
     */
    private function setupLogging(\ArrayObject $config)
    {
        /** @var \ArrayObject $logging */
        if ($logging = $config->offsetExists('logging') ? $config->offsetGet('logging') : false) {
            if (array_key_exists('file', $logging) && !empty($logging['file'])) {
                parent::offsetSet('monolog', parent::share(parent::extend('monolog', function (Logger $monolog, Application $app) use ($logging) {
                    $monolog->pushHandler(new StreamHandler($logging['file'], MonologServiceProvider::translateLevel($app['monolog.level']), $app['monolog.bubble'], $app['monolog.permission']));

                    return $monolog;
                })));
            }

            if (array_key_exists('syslog', $logging) && $logging['syslog'] === true) {
                parent::offsetSet('monolog', parent::share(parent::extend('monolog', function (Logger $monolog, Application $app) {
                    $monolog->pushHandler(new SyslogHandler($app['monolog.name'], LOG_USER, Logger::DEBUG, $app['monolog.bubble']));

                    return $monolog;
                })));
            }
        }
    }

    /**
     * Setup Logging
     *
     * @param \ArrayObject $config
     */
    private function setupDatabase(\ArrayObject $config)
    {
        /** @var \ArrayObject $database */
        if ($database = $config->offsetExists('database') ? $config->offsetGet('database') : false && array_key_exists('path', $database)) {
            $this['db.options'] = array(
                'driver' => 'pdo_sqlite',
                'path'   => $database['path'],
            );
        }
    }
}