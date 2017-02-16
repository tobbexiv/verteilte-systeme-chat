<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="message",indexes={@ORM\Index(name="uuid_idx", columns={"uuid"}),@ORM\Index(name="sent_idx", columns={"sent"})})
 */
class Message
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="username", referencedColumnName="username")
     */
    private $user;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $sent;
    
    /**
     * @ORM\Column(type="text")
     */
    private $text;
    
    /**
     * @ORM\Column(type="string", length=23)
     */
    private $uuid;
    
    /**
     * @ORM\PrePersist()
     */
    public function generateUuid() {
        if (!$this->uuid) {
            $this->uuid = uniqid("", true);
        }
    }
    
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
     * Set user
     *
     * @param string $user
     *
     * @return Message
     */
    public function setUser($user)
    {
        if(!$this->user) {
            $this->user = $user;
        }

        return $this;
    }

    /**
     * Get user
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->user->getUsername();
    }

    /**
     * Set sent
     *
     * @param \DateTime $sent
     *
     * @return Message
     */
    public function setSent($sent)
    {
        $this->sent = $sent;

        return $this;
    }

    /**
     * Get sent
     *
     * @return \DateTime
     */
    public function getSent()
    {
        return $this->sent;
    }

    /**
     * Set text
     *
     * @param string $text
     *
     * @return Message
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set uuid
     *
     * @param string $uuid
     *
     * @return Message
     */
    public function setUuid($uuid)
    {
        if (!$this->uuid) {
            $this->uuid = $uuid;
        }
        

        return $this;
    }

    /**
     * Get uuid
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }
}
