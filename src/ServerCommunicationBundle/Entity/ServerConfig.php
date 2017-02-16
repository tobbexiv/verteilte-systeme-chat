<?php

namespace ServerCommunicationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="serverConfig")
 */
class ServerConfig {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $configName;
    
    /**
     * @ORM\Column(type="text")
     */
    private $configValue;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $valueHashed;

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
     * Set configName
     *
     * @param string $configName
     *
     * @return ServerConfig
     */
    public function setConfigName($configName)
    {
        $this->configName = $configName;

        return $this;
    }

    /**
     * Get configName
     *
     * @return string
     */
    public function getConfigName()
    {
        return $this->configName;
    }

    /**
     * Set configValue
     *
     * @param string $configValue
     *
     * @return ServerConfig
     */
    public function setConfigValue($configValue, $valueHashed = false)
    {
        $this->configValue = $valueHashed ? hash('sha512', $configValue) : $configValue;
        $this->valueHashed = $valueHashed;

        return $this;
    }

    /**
     * Get configValue
     *
     * @return string
     */
    public function getConfigValue()
    {
        return $this->configValue;
    }
    
    /**
     * Check configValue
     *
     * @return string
     */
    public function checkConfigValue($valueToCheck) {
        $valueToCheck = $this->valueHashed ? hash('sha512', $valueToCheck) : $valueToCheck;
        return  $this->configValue == $valueToCheck;
    }
}
