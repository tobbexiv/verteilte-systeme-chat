<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Rest\NamePrefix("chat_")
 */
class ChatController extends FOSRestController
{
    /**
     * @Rest\Route(defaults={"_format"="html"})
     */
    public function getAction(Request $request)
    {
        if (!$this->get('session')->has('username')) {
            $view = $this->routeRedirectView('user_get', array(), 303);
        } else {
            $view = $this->view(null, 200)
                        ->setTemplate('AppBundle::chat.html.twig');
        }
        
        return $this->handleView($view);
    }
}