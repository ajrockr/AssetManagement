<?php

namespace App\EventSubscriber;

use App\Repository\AlertMessageRepository;
use App\Repository\SiteConfigRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class AlertMessageSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly Environment $twig, private readonly AlertMessageRepository $alertMessage) {}

    public function onKernelController(ControllerEvent $event): void
    {
        if ($this->alertMessage->alertsEnabled()) {
            $this->twig->addGlobal('areAlerts', true);

            if ($messages = $this->alertMessage->getActiveMessages()) {
                $this->twig->addGlobal('alertMessages', $messages);
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
