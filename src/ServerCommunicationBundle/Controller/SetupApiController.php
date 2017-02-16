<?php

namespace ServerCommunicationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use ServerCommunicationBundle\Entity\ServerConfig;
use ServerCommunicationBundle\Entity\Server;
use ServerCommunicationBundle\Event\ServerCommunicationSetupEvent;
use Buzz\Exception\RequestException;

/**
 * @Rest\Prefix("/api")
 * @Rest\NamePrefix("server_api_")
 */
class SetupApiController extends FOSRestController
{
    public function putConfigAction (Request $request) {
        $data = $request->request;
        $repository = $this->getDoctrine()->getRepository('ServerCommunicationBundle:ServerConfig');
        
        if($repository->findOneByConfigName('secureToken')) {
            throw new HttpException(403, "The config is already set up!");
        } else {
            $ownUri = $data->get('ownUri');
            $secureToken = $data->get('secureToken');
            $identifyToken = $data->get('identifyToken');
            
            if (!$ownUri || !$secureToken || !$identifyToken) {
                throw new HttpException(400, "Please enter a secure and an identify token and the own server uri");
            } elseif ($secureToken == $identifyToken) {
                throw new HttpException(400, "Secure and a identify token must differ");
            } elseif ($secureToken != $data->get('secureTokenConfirm') || $identifyToken != $data->get('identifyTokenConfirm')) {
                throw new HttpException(400, "The confirmation of secure and/or identify token is not correct");
            } else {
                $serverConfigOwnUri = new ServerConfig();
                $serverConfigOwnUri->setConfigName('ownUri');
                $serverConfigOwnUri->setConfigValue($ownUri);
                
                $serverConfigSecureToken = new ServerConfig();
                $serverConfigSecureToken->setConfigName('secureToken');
                $serverConfigSecureToken->setConfigValue($secureToken, true);
                
                $serverConfigIdentifyToken = new ServerConfig();
                $serverConfigIdentifyToken->setConfigName('identifyToken');
                $serverConfigIdentifyToken->setConfigValue($identifyToken);
                
                $em = $this->getDoctrine()->getManager();
                $em->persist($serverConfigOwnUri);
                $em->persist($serverConfigSecureToken);
                $em->persist($serverConfigIdentifyToken);
                $em->flush();
                
                $view = $this->view([ 'redirectRoute' => 'server_get' ], 200);
            }
        }
        
        return $this->handleView($view);
    }
    
    /**
     * Use a post to get the servers as otherwise the token is part of the uri.
     * Not a good behavior, but better than writing a secure token in the history.
     */
    public function cpostServersAction (Request $request)
    {
        $data = $request->request;
    
        $this->checkTokenValid($data->get('secureToken'));
        
        $repository = $this->getDoctrine()->getRepository('ServerCommunicationBundle:Server');
        
        $servers = $repository->findAll();
    
        $view = $this->view($servers, 200);
    
        return $this->handleView($view);
    }
    
    public function putServerAction (Request $request)
    {
        $data = $request->request;
    
        $this->checkTokenValid($data->get('secureToken'));
        
        $uri = $data->get('uri');
        $token = $data->get('serverToken');
        
        if(!$uri || !$token) {
            throw new HttpException(400, "You must provide uri and server token");
        } elseif ($token != $data->get('serverTokenConfirm')) {
            throw new HttpException(400, "The provided server tokens are not equal");
        } else {
            $repository = $this->getDoctrine()->getRepository('ServerCommunicationBundle:Server');

            if(!$repository->findOneByUri($uri)) {
                $server = new Server();
                $server->setUri($uri);
                $server->setToken($token);

                $em = $this->getDoctrine()->getManager();
                $em->persist($server);
                $em->flush();
                
                $configRepository = $this->getDoctrine()->getRepository('ServerCommunicationBundle:ServerConfig');
                
                $url = $server->getUri() . $this->get('router')->generate('server_put');
                $content = $this->get('serializer')->serialize([
                    'identifyToken' => $server->getToken(),
                    'uri' => $configRepository->findOneByConfigName('ownUri')->getConfigValue(),
                    'token' => $configRepository->findOneByConfigName('identifyToken')->getConfigValue()
                ], 'json');
                $header = [
                    'Connection' => 'Close',
                    'Content-Type' => 'application/json',
                    'Content-Length' => strlen($content)
                ];
                try {
                    $this->get('buzz')->put($url, $header, $content);
                } catch (RequestException $e) {}
                
                $event = new ServerCommunicationSetupEvent();
                $this->get('event_dispatcher')->dispatch('serverCommunication.setup', $event);
                
                $view = $this->view($server, 200);
            } else {
                $view = $this->view([], 200);
            }
        }
        
        return $this->handleView($view);
    }
    
    public function deleteServerAction (Request $request, $id)
    {
        $data = $request->request;
    
        $this->checkTokenValid($data->get('secureToken'));
        
        if(!$id) {
            throw new HttpException(400, "Deletion not possible: no server id given");
        } else {
            $repository = $this->getDoctrine()->getRepository('ServerCommunicationBundle:Server');

            $server = $repository->find($id);

            if($server) {
                $configRepository = $this->getDoctrine()->getRepository('ServerCommunicationBundle:ServerConfig');
                
                $url = $server->getUri() . $this->get('router')->generate('server_delete');
                $content = $this->get('serializer')->serialize([
                    'identifyToken' => $server->getToken(),
                    'uri' => $configRepository->findOneByConfigName('ownUri')->getConfigValue()
                ], 'json');
                $header = [
                    'Connection' => 'Close',
                    'Content-Type' => 'application/json',
                    'Content-Length' => strlen($content)
                ];
                try {
                    $this->get('buzz')->delete($url, $header, $content);
                } catch (RequestException $e) {}
                
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
    
        if(!$repository->findOneByConfigName('secureToken')->checkConfigValue($token)) {
            throw new HttpException(403, "Your secure token is not valid");
        }
    }
}