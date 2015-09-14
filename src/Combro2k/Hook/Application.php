<?php
namespace Combro2k\Hook;

use Combro2k\Hook\Controller\Controller;
use Combro2k\Hook\Controller\HooksController;
use Silex\Application as BaseApplication;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * The Silex framework class.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
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
        $this->beforeFilters();
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
        $this['controller'] = $this->share(function ($app) {
            return new Controller($app);
        });

        $this['hooks.controller'] = $this->share(function ($app) {
            return new HooksController($app);
        });

        $this->error(function (\Exception $e) {
            return $this['controller']->notHereAction();
        });
    }

    /**
     * Setup routes
     */
    public function setupRoutes()
    {
        /** Hooks route */
        $this->get('/hooks/{uid}', 'hooks.controller:indexAction')->method('POST');
    }

    /**
     *
     */
    public function beforeFilters()
    {
        $this->before(function (Request $request) {
            if ($request->getMethod() === 'GET') {
                return true;
            } elseif (false !== strpos($request->headers->get('Content-Type'), 'application/json')) {
                return new JsonResponse(array('error' => 'Forbidden'), JsonResponse::HTTP_FORBIDDEN);
            } elseif (!($data = $request->getContent())) {
                return new JsonResponse(array('error' => 'No data!'), JsonResponse::HTTP_BAD_REQUEST);
            } elseif (!($data = json_decode($data, true))) {
                return new JsonResponse(array('error' => 'Can not decode JSON'), JsonResponse::HTTP_BAD_REQUEST);
            }

            $request->request->replace(is_array($data) ? $data : array());

            return true;
        }, 0);
    }
}