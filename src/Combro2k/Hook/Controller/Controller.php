<?php

namespace Combro2k\Hook\Controller;

use Combro2k\Hook\Application;
use Symfony\Component\HttpFoundation\Response;

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
    public function notHereAction()
    {
        return $this->getApp()
            ->render('index.twig', array());
    }
}