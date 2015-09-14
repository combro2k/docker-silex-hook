<?php
namespace Combro2k\Hook;

use Combro2k\Hook\Controller\Controller;
use Combro2k\Hook\Controller\HooksController;
use Combro2k\Hook\Provider\HooksControllerProvider;
use Silex\Application as BaseApplication;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The Silex framework class.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */

/** @noinspection PhpInconsistentReturnPointsInspection */
class Application extends BaseApplication
{
    use BaseApplication\TwigTrait;
    use BaseApplication\SecurityTrait;
    use BaseApplication\UrlGeneratorTrait;
    use BaseApplication\SwiftmailerTrait;
    use BaseApplication\MonologTrait;

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

        parent::boot();
    }

    /**
     * Register all providers we want to use
     */
    public function registerProviders()
    {
        $this->register(new ValidatorServiceProvider());
        $this->register(new MonologServiceProvider(), array('monolog.logfile' => sprintf('%s/%s', $this['rootPath'], 'logs/development.log')));
        $this->register(new TwigServiceProvider(), array('twig.path' => sprintf('%s/%s', $this['rootPath'], 'src/Combro2k/Hook/Resources/Views/')));
        $this->register(new ServiceControllerServiceProvider());
        $this->register(new UrlGeneratorServiceProvider());
        $this->register(new DoctrineServiceProvider(), array('db.options' => array('driver' => 'pdo_sqlite', 'path' => __DIR__.'/app.db')));
    }

    /**
     * Setup all route paths we want to use
     */
    public function setupControllers()
    {
        $this['controller'] = parent::share(function ($app) {
            return new Controller($app);
        });

        $this['hooks.controller'] = parent::share(function ($app) {
            return new HooksController($app);
        });

        $this->error(function (\Exception $e) {
            return $this['controller']->indexAction();
        });
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
     * Setup routes
     */
    public function setupRoutes()
    {
        /** Hooks route */
        $this->mount('/hooks', new HooksControllerProvider());
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
            //return new JsonResponse($response->getContent(), $response->getStatusCode(), $response->headers->all());
        }

        return $response;
    }
}