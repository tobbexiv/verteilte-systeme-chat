<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * @Rest\Prefix("/user")
 * @Rest\NamePrefix("user_")
 */
class UserController extends FOSRestController {
    /**
     * @Rest\Route(defaults={"_format"="html"})
     */
    public function getAction() {
        if (!$this->get('session')->has('username')) {
            $view = $this->view(null, 200)
                        ->setTemplate('AppBundle:User:access.html.twig');
        } else {
            $view = $this->routeRedirectView('chat_get', array(), 303);
        }
        
        return $this->handleView($view);
    }
    
    public function getLogoutAction() {
        $this->get('session')->invalidate();
        
        $view = $this->routeRedirectView('user_get', array(), 303);
        
        return $this->handleView($view);
    }
}