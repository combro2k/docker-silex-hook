<?php

namespace Combro2k\Hook\Provider;

use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ConfigServiceProvider
 * @package Combro2k\Hook\Provider
 */
class ConfigServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param BaseApplication $app
     */
    public function register(BaseApplication $app)
    {
        $app['config'] = $app->share(function () use ($app) {
            if (!file_exists($app['config.path'])) {
                $result = array();
            } else {
                switch (pathinfo($app['config.path'], PATHINFO_EXTENSION)) {
                    case 'yaml':
                    case 'yml':
                        $result = Yaml::parse(file_get_contents($app['config.path'])) ?: array();
                        break;
                    default:
                        throw new \InvalidArgumentException('Unable to load configuration; the provided file extension was not recognized. '.'Only yml allowed');
                        break;
                }
            }

            return new \ArrayObject($result);
        });
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     *
     * @param \Silex\Application $app
     */
    public function boot(BaseApplication $app)
    {
        /** @var \Combro2k\Hook\Application $app */
        $app->setConfig($app['config']);
    }
}