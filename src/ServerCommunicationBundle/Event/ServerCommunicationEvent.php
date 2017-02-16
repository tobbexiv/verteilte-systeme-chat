<?php

namespace ServerCommunicationBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class ServerCommunicationEvent extends Event
{
    private $payloadType;
    private $payload;
    private $timeout;
    
    public function __construct($payloadType, $payload, $timeout = 1) {
        $this->payloadType = $payloadType;
        $this->payload = $payload;
        $this->timeout = $timeout;
    }

    public function getPayloadType()
    {
        return $this->payloadType;
    }
    
    public function getPayload() {
        return $this->payload;
    }
    
    public function getTimeout() {
        return $this->timeout;
    }
}