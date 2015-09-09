<?php
use Silex\Application as BaseApplication;

class Application extends BaseApplication
{
    use Silex\Application\TwigTrait;
    use Silex\Application\SecurityTrait;
    use Silex\Application\UrlGeneratorTrait;
    use Silex\Application\SwiftmailerTrait;
    use Silex\Application\MonologTrait;
}