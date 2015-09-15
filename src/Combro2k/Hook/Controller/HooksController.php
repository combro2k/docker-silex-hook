<?php

namespace Combro2k\Hook\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class HooksController
 * @package Combro2k\Hook\Controller
 */
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
        if (!$repository = $request->get('repository')) {
            return new Response('malformed data', Response::HTTP_BAD_REQUEST);
        } elseif ($repository['owner'] !== $this->get('owner')) {
            return new Response('Owner not authorized!', Response::HTTP_FORBIDDEN);
        }

        $this->log('Received hook', $repository);

        if ($callback_url = $request->get('callback_url', false)) {
            $this->log(sprintf('Trigger hook: %s', $callback_url));

            return $callback_url;
        }
    }
}
