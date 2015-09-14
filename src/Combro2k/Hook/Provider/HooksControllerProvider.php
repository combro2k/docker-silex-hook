<?php
namespace Combro2k\Hook\Provider;

use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class HooksControllerProvider
 * @package Combro2k\Hook\Provider
 */
class HooksControllerProvider implements ControllerProviderInterface
{
    /**
     * Returns routes to connect to the given application.
     *
     * @param Application $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app)
    {
        /** @var \Silex\ControllerCollection $factory */
        $factory = $app['controllers_factory'];

        $factory->post('/{token}', 'hooks.controller:postByUidAction')
            ->before(function (Request $request, Application $app) {
                /** @var \Combro2k\Hook\Application $app */
                return $app->beforeJsonFilter($request);
            })
            ->bind('postByUidAction');

        return $factory;
    }
}