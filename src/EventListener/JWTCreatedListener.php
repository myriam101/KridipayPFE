<?php
namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class JWTCreatedListener
{
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        /** @var UserInterface $user */
        $user = $event->getUser();

        $payload = $event->getData();
        $payload['roles'] = $user->getRoles(); // Inclure les rôles dans le token

        $event->setData($payload);
    }
}
