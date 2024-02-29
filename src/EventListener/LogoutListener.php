<?php

namespace App\EventListener;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutListener
{
    private $enttiyManager;

    public function __construct(EntityManagerInterface $enttiyManager)
    {
        $this->enttiyManager = $enttiyManager;
    }

    #[AsEventListener(event: LogoutEvent::class)]
    public function onSymfonyComponentSecurityHttpEventLogoutEvent(LogoutEvent $event)
    {
       $user = $event->getToken()->getUser();
       if($user) {
        $user->setIsVerified(false);
        $this->enttiyManager->flush();
       }
    }
}