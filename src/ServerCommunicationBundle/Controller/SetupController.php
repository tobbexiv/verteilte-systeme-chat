<?php

namespace ServerCommunicationBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use ServerCommunicationBundle\Entity\Server;

/**
 * @Rest\NamePrefix("server_")
 */
class SetupController extends FOSRestController
{
    /**
     * @Rest\Route(defaults={"_format"="html"})
     */
    public function cgetAction () {
        $repository = $this->getDoctrine()->getRepository('ServerCommunicationBundle:ServerConfig');
        
        if($repository->findOneByConfigName('secureToken')) {
            $view = $this->view(null, 200)
                         ->setTemplate('ServerCommunicationBundle::server.html.twig');
        } else {
            $view = $this->routeRedirectView('server_get_config', array(), 303);
        }
        
        return $this->handleView($view);
    }
    
    /**
     * @Rest\Route(defaults={"_format"="html"})
     */
    public function getConfigAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('ServerCommunicationBundle:ServerConfig');
        
        if(!$repository->findOneByConfigName('secureToken')) {
            $view = $this->view(null, 200)
                         ->setTemplate('ServerCommunicationBundle::config.html.twig');
        } else {
            throw new HttpException(403);
        }
        
        return $this->handleView($view);
    }
    
    public function putAction (Request $request)
    {
        $data = $request->request;
        
        $this->checkTokenValid($data->get('identifyToken'));
        
        $uri = $data->get('uri');
        $token = $data->get('token');
        
        if(!$uri || !$token) {
            throw new HttpException(400);
        } else {
            $repository = $this->getDoctrine()->getRepository('ServerCommunicationBundle:Server');
            
            if(!$server = $repository->findOneByUri($uri)) {
                $server = new Server();
                $server->setUri($uri);
                $server->setToken($token);
                
                $em = $this->getDoctrine()->getManager();
                $em->persist($server);
                $em->flush();
            }
            
            $view = $this->view([], 200);
        }
        
        return $this->handleView($view);
    }
    
    public function deleteAction (Request $request)
    {
        $data = $request->request;
        
        $this->checkTokenValid($data->get('identifyToken'));
        
        if(!$uri = $data->get('uri')) {
            throw new HttpException(400);
        } else {
            $repository = $this->getDoctrine()->getRepository('ServerCommunicationBundle:Server');
            
            $server = $repository->findOneByUri($uri);
            
            if($server) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($server);
                $em->flush();
            }
            
            $view = $this->view([], 200);
        }
        
        return $this->handleView($view);
    }
    
    protected function checkTokenValid ($token) {
        $repository = $this->getDoctrine()->getRepository('ServerCommunicationBundle:ServerConfig');
        
        if(!$repository->findOneByConfigName('identifyToken')->checkConfigValue($token)) {
            throw new HttpException(403);
        }
    }
}
