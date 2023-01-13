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
    private Environment $twig;
    private AlertMessageRepository $alertMessage;
    private SiteConfigRepository $siteConfig;

    public function __construct(Environment $twig, AlertMessageRepository $alertMessage, SiteConfigRepository $siteConfig)
    {
        $this->twig = $twig;
        $this->alertMessage = $alertMessage;
        $this->siteConfig = $siteConfig;
    }

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
