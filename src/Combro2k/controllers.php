<?php

use Combro2k\Hook\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/** @var Combro2k\Hook\Application $app */
Request::setTrustedProxies(array('127.0.0.1', '192.168.1.0/24', '172.17.41.0/16'));

/* Default /$ */
$app->get('/', function (Application $app) {
    return $app->render('index.twig', array());
})
    ->bind('homepage');

$app->post('/hook', function (Application $app, Request $request) {
    if (!$repository = $request->get('repository', false)) {
        return new JsonResponse(array('error' => 'malformed data'), JsonResponse::HTTP_BAD_REQUEST);
    } elseif (false !== strpos($repository['owner'], '_me_')) {
        return new JsonResponse(array('error' => 'Owner not authorized!'), JsonResponse::HTTP_FORBIDDEN);
    }

    $app->log('Received hook', $repository);

    if ($callback_url = $request->get('callback_url', false)) {
        $app->log('Trigger hook', array('callback_url' => $callback_url));

        return 'Hook';
    }

    return 'OK';
})
    ->bind('hook');


$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    return new JsonResponse(array('code' => $code), $code);
});
