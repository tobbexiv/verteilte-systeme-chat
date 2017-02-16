<?php

namespace AppBundle\EventSubscriber;

use ServerCommunicationBundle\Event\ServerCommunicationEvent;
use ServerCommunicationBundle\Event\ServerCommunicationSetupEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\User;
use AppBundle\Entity\Message;

class ServerCommunicationSubscriber implements EventSubscriberInterface
{
    private $eventDispatcher;
    private $entityManager;
    
    private $maximumMessagesToFetch = 1000;
    
    public function __construct($eventDispatcher, $entityManager) {
        $this->eventDispatcher = $eventDispatcher;
        $this->entityManager = $entityManager;
    }
    
    public static function getSubscribedEvents()
    {
        return [
            'serverCommunication.setup' => [
                ['onServerSetupEvent', 0]
            ],
            'serverCommunication.receive.user' => [
                ['onServerCommunicationUserEvent', 0]
            ],
            'serverCommunication.receive.message' => [
                ['onServerCommunicationMessageEvent', 0]
            ],
            'serverCommunication.receive.massUsers' => [
                ['onServerCommunicationMassUserEvent', 0]
            ],
            'serverCommunication.receive.massMessages' => [
                ['onServerCommunicationMassMessageEvent', 0]
            ],
            'serverCommunication.receive.fetchUsers' => [
                ['onServerCommunicationFetchUserEvent', 0]
            ],
            'serverCommunication.receive.fetchMessages' => [
                ['onServerCommunicationFetchMessageEvent', 0]
            ]
        ];
    }
    
    public function onServerSetupEvent(ServerCommunicationSetupEvent $event)
    {
        $event = new ServerCommunicationEvent('fetchUsers', '');
        $this->eventDispatcher->dispatch('serverCommunication.send', $event);
        
        $event = new ServerCommunicationEvent('fetchMessages', $this->maximumMessagesToFetch);
        $this->eventDispatcher->dispatch('serverCommunication.send', $event);
    }
    
    public function onServerCommunicationUserEvent(ServerCommunicationEvent $event)
    {
        $userData = $event->getPayload();
        
        $repository = $this->entityManager->getRepository('AppBundle:User');
        
        $user = $repository->find($userData['username']);
        
        if (!$user) {
            $user = new User();
            $user->setUsername($userData['username'])
                 ->setPassword($userData['password'], true);
            
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            
            $this->eventDispatcher->dispatch('serverCommunication.send', $event);
        }
    }
    
    public function onServerCommunicationMessageEvent(ServerCommunicationEvent $event)
    {
        $messageData = $event->getPayload();
        
        $repository = $this->entityManager->getRepository('AppBundle:Message');
        
        $message = $repository->findOneByUuid($messageData['uuid']);
        
        if (!$message) {
            $userRepository = $this->entityManager->getRepository('AppBundle:User');
            
            $user = $userRepository->find($messageData['username']);
            
            if($user) {
                $message = new Message();
                $message->setUser($user)
                        ->setText($messageData['text'])
                        ->setSent(new \DateTime($messageData['sent']))
                        ->setUuid($messageData['uuid']);
            
                $this->entityManager->persist($message);
                $this->entityManager->flush();
                
                $this->eventDispatcher->dispatch('serverCommunication.send', $event);
            }
        }
    }
    
    public function onServerCommunicationMassUserEvent(ServerCommunicationEvent $event)
    {
        $userMassData = $event->getPayload();
        
        $repository = $this->entityManager->getRepository('AppBundle:User');
        
        foreach ($userMassData as $userData) {
            $user = $repository->find($userData['username']);
            
            if (!$user) {
                $user = new User();
                $user->setUsername($userData['username'])
                     ->setPassword($userData['password'], true);
            
                $this->entityManager->persist($user);
            }
        }
        
        $this->entityManager->flush();
    }
    
    public function onServerCommunicationMassMessageEvent(ServerCommunicationEvent $event)
    {
        $messageMassData = $event->getPayload();
        
        $repository = $this->entityManager->getRepository('AppBundle:Message');
        $userRepository = $this->entityManager->getRepository('AppBundle:User');
        
        $userSelection = [];
        
        foreach ($messageMassData as $messageData) {
            $message = $repository->findOneByUuid($messageData['uuid']);
            
            if (!$message) {
                if(!isset($userSelection[$messageData['username']])) {
                    $user = $userRepository->find($messageData['username']);
                    $userSelection[$messageData['username']] = $user;
                } else {
                    $user = $userSelection[$messageData['username']];
                }
            
                if($user) {
                    $message = new Message();
                    $message->setUser($user)
                            ->setText($messageData['text'])
                            ->setSent(new \DateTime($messageData['sent']))
                            ->setUuid($messageData['uuid']);
            
                    $this->entityManager->persist($message);
                }
            }
        
            $this->entityManager->flush();
        }
    }
    
    public function onServerCommunicationFetchUserEvent(ServerCommunicationEvent $event) {
        $repository = $this->entityManager->getRepository('AppBundle:User');
        
        $users = $repository->findAll();
        
        $event = new ServerCommunicationEvent('massUsers', $users, 5);
        $this->eventDispatcher->dispatch('serverCommunication.send', $event);
    }
    
    public function onServerCommunicationFetchMessageEvent(ServerCommunicationEvent $event) {
        $maximumNumber = intval($event->getPayload());
        
        $repository = $this->entityManager->getRepository('AppBundle:Message');
        
        $messages = $repository->createQueryBuilder('m')
                            ->orderBy('m.sent', 'DESC')
                            ->getQuery()
                            ->setMaxResults($maximumNumber)
                            ->getResult();
        
        $event = new ServerCommunicationEvent('massMessages', $messages, 5);
        $this->eventDispatcher->dispatch('serverCommunication.send', $event);
    }
}