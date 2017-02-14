<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=50)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $password;

    /**
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        if(!$this->username) {
            $this->username = $username;
        }

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password, $passwordHashed = false)
    {
        $this->password = $passwordHashed ? $password : hash('sha512', $password);

        return $this;
    }

    /**
     * Check password and return if it matches.
     *
     * @return boolean
     */
    public function checkPassword($passwordToCheck)
    {
        return $this->password == hash('sha512', $passwordToCheck);
    }
}
