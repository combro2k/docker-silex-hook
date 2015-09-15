<?php
namespace Combro2k\Hook\Provider;

use Combro2k\Hook\Application;
use Silex\Application as BaseApplication;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;

/**
 * Class HooksControllerProvider
 * @package Combro2k\Hook\Provider
 */
class HooksControllerProvider implements ControllerProviderInterface
{
    /**
     * Returns routes to connect to the given application.
     *
     * @param BaseApplication $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(BaseApplication $app)
    {
        /** @var Application $app */
        $factory = $app->getControllerFactory();

        $factory->post('/{token}', 'hooks.controller:postByUidAction')
                ->before('hooks.controller:beforeJsonFilter')
                ->after('hooks.controller:afterJsonFilter')
                ->bind('postByUidAction');

        return $factory;
    }
}