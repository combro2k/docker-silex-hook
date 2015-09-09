<?php

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/** @var \Application $app */
Request::setTrustedProxies(array('127.0.0.1', '192.168.1.0/24', '172.17.41.0/16'));

$app->before(function (Request $request) {
    if ($request->getMethod() === 'GET') {
        return true;
    }

    if (!strstr($request->headers->get('Content-Type'), 'application/json')) {
        return new JsonResponse(array('error' => 'Forbidden'), JsonResponse::HTTP_FORBIDDEN);
    } elseif (!($data = $request->getContent())) {
        return new JsonResponse(array('error' => 'No data!'), JsonResponse::HTTP_BAD_REQUEST);
    } elseif (!($data = json_decode($data, true))) {
        return new JsonResponse(array('error' => 'Can not decode JSON'), JsonResponse::HTTP_BAD_REQUEST);
    }

    $request->request->replace(is_array($data) ? $data : array());

    return true;
}, 0);

/* Default /$ */
$app->get('/', function (Application $app) {
    return $app->render('index.twig', array());
})->bind('homepage');

$app->post('/hook', function (Application $app, Request $request) {
    if (!$repository = $request->get('repository', false)) {
        return new JsonResponse(array('error' => 'malformed data'), JsonResponse::HTTP_BAD_REQUEST);
    } elseif (!strstr($repository['owner'], '_me_')) {
        return new JsonResponse(array('error' => 'Owner not authorized!'), JsonResponse::HTTP_FORBIDDEN);
    }

    $app->log('Received hook', $repository);

    if ($callback_url = $request->get('callback_url', false)) {
        $app->log('Trigger hook', array('callback_url' => $callback_url));

        return 'Hook';
    }

    return 'OK';
})->bind('hook');


$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    return new JsonResponse(array('code' => $code), $code);
});
