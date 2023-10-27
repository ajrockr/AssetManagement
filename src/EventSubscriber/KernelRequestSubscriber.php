<?php

namespace App\EventSubscriber;

use App\Entity\SiteConfig;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class KernelRequestSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;
    private Security $security;
    private UrlGeneratorInterface $urlGenerator;
    private UserRepository $userRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        Security $security,
        UrlGeneratorInterface $urlGenerator,
        UserRepository $userRepository
    ) {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->urlGenerator = $urlGenerator;
        $this->userRepository = $userRepository;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // Check if maintenance mode was enabled
        $isMaintenance = $this->entityManager->getRepository(SiteConfig::class)->findOneByName('site_maintenanceModeEnabled')->getConfigValue();

        // Check if we are cli
        $isCli = \PHP_SAPI === 'cli';

        // Get current route
        $route = $event->getRequest()->attributes->get('_route');

        // If maintenance mode & notCli & not app_login route & not ROLE_ADMIN, we are in maintenance mode. Redirect to login
        if( ($isMaintenance == "1" && !$isCli && ($route != "app_login" || $route != "admin")) && !$this->security->isGranted('ROLE_USER')) {
            $event->setResponse(new RedirectResponse(
                $this->urlGenerator->generate('app_login')
            ));
        }

        $userId = $this->security->getUser()->getId();
        $this->userRepository->setLastActiveTime($userId, new \DateTimeImmutable('now'));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
