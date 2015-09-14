<?php

namespace Combro2k\Hook\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HooksController extends Controller
{
    /**
     * @param Request $request
     * @param string  $uid
     *
     * @return Response
     */
    public function indexAction(Request $request, $uid)
    {
        return new Response($uid);
    }
}