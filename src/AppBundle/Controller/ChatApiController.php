<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use AppBundle\Entity\Message;
use AppBundle\Entity\User;

/**
 * @Rest\Prefix("/api")
 * @Rest\NamePrefix("chat_api_")
 */
class ChatApiController extends FOSRestController
{
    private $maximumInitialMessages = 100;
    
    public function cgetMessagesAction()
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:Message');
        
        $messages = $repository->createQueryBuilder('m')
                            ->orderBy('m.sent', 'DESC')
                            ->getQuery()
                            ->setMaxResults($this->maximumInitialMessages)
                            ->getResult();
        
        $view = $this->view($messages, 200);
        
        return $this->handleView($view);
    }
    
    /**
     * @Rest\Get("/messages/fromId/{id}")
     */
    public function cgetMessagesFromIdAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:Message');
        
        $messages = $repository->createQueryBuilder('m')
                            ->where('m.id > :id')
                            ->setParameter('id', $id)
                            ->getQuery()
                            ->getResult();
        
        $view = $this->view($messages, 200);
        
        return $this->handleView($view);
    }
    
    public function postMessageAction(Request $request) {
        if(!$this->get('session')->has('username')) {
            $view = $this->view([ 'redirectRoute' => 'user_get' ], 200);
        } else {
            $session = $this->get('session');
            $data = $request->request;
            
            $repository = $this->getDoctrine()->getRepository('AppBundle:User');
            
            $user = $repository->find($session->get('username'));
            
            $message = new Message();
            $message->setUser($user)
                    ->setText($data->get('text'))
                    ->setSent(new \DateTime());
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();
            
            $view = $this->view($message, 200);
        }
        
        return $this->handleView($view);
    }
}