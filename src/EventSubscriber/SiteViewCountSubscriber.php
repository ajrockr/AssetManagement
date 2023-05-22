<?php

namespace App\EventSubscriber;

use App\Entity\SiteView;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SiteViewCountSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        // dd($event->getRequest()->attributes->get('_route'));
        if ($event->getRequest()->attributes->get('_route') === 'app_home')
            $this->entityManager->getRepository(SiteView::class)->updateCounter();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
