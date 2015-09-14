<?php

namespace Combro2k\Hook\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class HooksController extends Controller
{
    /**
     * @param Request $request
     * @param string  $token
     *
     * @return JsonResponse
     */
    public function postByUidAction(Request $request, $token)
    {
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
}
