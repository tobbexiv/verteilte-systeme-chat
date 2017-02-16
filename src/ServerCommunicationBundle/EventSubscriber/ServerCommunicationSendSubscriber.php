<?php

namespace ServerCommunicationBundle\EventSubscriber;

use ServerCommunicationBundle\Event\ServerCommunicationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Doctrine\ORM\EntityManager;
use Buzz\Exception\RequestException;

class ServerCommunicationSendSubscriber implements EventSubscriberInterface
{
    private $eventDispatcher;
    private $entityManager;
    private $router;
    private $browser;
    private $serializer;

    public function __construct($eventDispatcher, $entityManager, $router, $browser, $serializer) {
        $this->eventDispatcher = $eventDispatcher;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->browser = $browser;
        $this->serializer = $serializer;
    }

    public static function getSubscribedEvents()
    {
        return [
            'serverCommunication.send' => [
                ['onServerCommunicationSendEvent', 0]
            ]
        ];
    }

    public function onServerCommunicationSendEvent(ServerCommunicationEvent $event)
    {
        $path = $this->router->generate('server_communication_post');
        $repository = $this->entityManager->getRepository('ServerCommunicationBundle:Server');
        $this->browser->getClient()->setTimeout($event->getTimeout());

        $header = [
            'Connection' => 'Close',
            'Content-Type' => 'application/json',
            'Content-Length' => 0
        ];

        foreach($repository->findAll() as $server) {
            $url = $server->getUri() . $path;
            $content = $this->serializer->serialize([
                'token' => $server->getToken(),
                'payload' => $event
            ], 'json');
            $header['Content-Length'] = strlen($content);

            try {
                $this->browser->post($url, $header, $content);
            } catch (RequestException $e) {}
        }
    }
}