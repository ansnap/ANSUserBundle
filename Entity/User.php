<?php

namespace ANS\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\MappedSuperclass
 */
class User implements UserInterface, \Serializable
{

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="email", type="string", length=40, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(name="name", type="string", length=40)
     * @Assert\NotBlank()
     * @Assert\Length(min = "2", max = "40")
     */
    private $name;

    /**
     * @ORM\Column(name="salt", type="string", length=32)
     */
    private $salt;

    /**
     * @ORM\Column(name="password", type="string", length=64)
     */
    private $password;

    /**
     * @ORM\Column(name="plainpassword", type="string", length=30, nullable=true)
     * @Assert\Length(min = "6", max = "4096")
     */
    private $plainPassword;

    /**
     * @ORM\Column(name="role", type="string", length=1)
     * @Assert\NotBlank()
     */
    private $role;

    /**
     * Roles for security system (keys up to 1 symbol)
     */
    private $role_list = array(
        'u' => 'ROLE_USER',
        'm' => 'ROLE_MODERATOR',
        'a' => 'ROLE_ADMIN',
    );

    public function __construct()
    {
        $this->setPlainPassword($this->generatePassword());
        $this->setRole('ROLE_USER');
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $name
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $salt
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $plainPassword
     * @return User
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param string $role
     * @return User
     */
    public function setRole($role)
    {
        $this->role = array_search($role, $this->role_list);

        if (!$this->role) {
            throw new \InvalidArgumentException('Not valid user role');
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role_list[$this->role];
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return array($this->role_list[$this->role]);
    }

    public function eraseCredentials()
    {

    }

    public function getUsername()
    {
        return $this->email;
    }

    public function serialize()
    {
        return serialize(array(
            $this->id,
        ));
    }

    public function unserialize($serialized)
    {
        list (
                $this->id,
                ) = unserialize($serialized);
    }

    /**
     * Generate random password
     */
    public function generatePassword()
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
    }

}
