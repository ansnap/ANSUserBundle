<?php

namespace ANS\UserBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use ANS\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

class UserSubscriber implements EventSubscriber
{

    protected $encoderFactory;

    public function __construct(EncoderFactory $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'preUpdate',
        );
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $user = $args->getEntity();

        if (!($user instanceof User)) {
            return;
        }

        $this->updateCredentials($user);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $user = $args->getEntity();

        if (!($user instanceof User)) {
            return;
        }

        $this->updateCredentials($user);
    }

    public function updateCredentials(User $user)
    {
        $plainPassword = $user->getPlainPassword();

        if (!empty($plainPassword)) {
            $salt = md5(uniqid(null, true));
            $user->setSalt($salt);

            $encoder = $this->encoderFactory->getEncoder($user);
            $user->setPassword($encoder->encodePassword($plainPassword, $salt));

            $user->setPlainPassword(null);
        }
    }

}
