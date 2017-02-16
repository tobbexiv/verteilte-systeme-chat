<?php

namespace ServerCommunicationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use ServerCommunicationBundle\Event\ServerCommunicationEvent;

/**
 * @Rest\Prefix("/communication")
 * @Rest\NamePrefix("server_communication_")
 */
class ServerCommunicationController extends FOSRestController
{
    public function postAction (Request $request) {
        $data = $request->request;

        $repository = $this->getDoctrine()->getRepository('ServerCommunicationBundle:ServerConfig');
        $this->checkTokenValid($data->get('token'));

        $eventData = $data->get('payload');
        $type = $eventData['payloadType'];
        $payload = $eventData['payload'];

        $event = new ServerCommunicationEvent($type, $payload);
        $this->get('event_dispatcher')->dispatch('serverCommunication.receive.' . $type, $event);

        $view = $this->view([], 200);
        return $this->handleView($view);
    }

    protected function checkTokenValid ($token) {
        $repository = $this->getDoctrine()->getRepository('ServerCommunicationBundle:ServerConfig');

        if(!$repository->findOneByConfigName('identifyToken')->checkConfigValue($token)) {
            throw new HttpException(403, "Your identify token is not valid");
        }
    }
}