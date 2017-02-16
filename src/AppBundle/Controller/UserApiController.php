<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use ServerCommunicationBundle\Event\ServerCommunicationEvent;
use AppBundle\Entity\User;

/**
 * @Rest\Prefix("/user/api")
 * @Rest\NamePrefix("user_api_")
 */
class UserApiController extends FOSRestController {
    public function postLoginAction(Request $request) {
        if($this->get('session')->has('username')) {
            $view = $this->view([ 'redirectRoute' => 'chat_get' ], 200);
        } else {
            $data = $request->request;
            
            $repository = $this->getDoctrine()->getRepository('AppBundle:User');
            
            if ($data->get('username')) {
                $user = $repository->find($data->get('username'));
            }
            
            if ($user && $user->checkPassword($data->get('password'))) {
                $this->get('session')->set('username', $user->getUsername());
                
                $view = $this->view([ 'redirectRoute' => 'chat_get' ], 200);
            } else {
                throw new HttpException(400, 'Combination of username and password is not valid.');
            }
        }
        
        return $this->handleView($view);
    }
    
    public function putUserAction(Request $request) {
        if($this->get('session')->has('username')) {
            $view = $this->view([ 'redirectRoute' => 'chat_get' ], 200);
        } else {
            $data = $request->request;
            
            $repository = $this->getDoctrine()->getRepository('AppBundle:User');
            
            $user = $repository->find($data->get('username'));
        
            if ($user) {
                throw new HttpException(409, 'Username is already in use.');
            } elseif ($data->get('password') != $data->get('confirmPassword')) {
                throw new HttpException(400, 'The provided passwords are not equal');
            } elseif (!$data->get('username')) {
                throw new HttpException(400, 'You must enter a username');
            } else {
                $user = new User();
                $user->setUsername($data->get('username'))
                     ->setPassword($data->get('password'));
                
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
                
                $this->get('session')->set('username', $user->getUsername());
                
                $event = new ServerCommunicationEvent('user', $user);
                $this->get('event_dispatcher')->dispatch('serverCommunication.send', $event);
                
                $view = $this->view([ 'redirectRoute' => 'chat_get' ], 200);
            }
            
            return $this->handleView($view);
        }
    }
}