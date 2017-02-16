<?php

namespace ServerCommunicationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="server")
 */
class Server {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(type="text")
     */
    private $uri;
    
    /**
     * @ORM\Column(type="string", length=23)
     */
    private $token;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set uri
     *
     * @param string $uri
     *
     * @return Server
     */
    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * Get uri
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Set token
     *
     * @param string $token
     *
     * @return Server
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Check token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
}
