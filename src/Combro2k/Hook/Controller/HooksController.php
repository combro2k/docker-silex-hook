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
        return new Response('test', Response::HTTP_OK);
    }
}