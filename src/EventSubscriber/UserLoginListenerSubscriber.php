<?php

namespace App\EventSubscriber;

use App\Service\Logger;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

class UserLoginListenerSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly Logger $logger,
        private readonly RequestStack $requestStack) {}


    /**
     * Logs the user on successful login event
     * @param AuthenticationSuccessEvent $event
     * @return void
     */
    public function onSecurityAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();
        $this->logger->userLogin($user->getId(), $this->requestStack->getCurrentRequest()->getClientIp(), $user->getUserIdentifier(), true);
    }

    /**
     * Logs the user on failed login event
     * @param LoginFailureEvent $event
     * @return void
     */
    public function onSecurityAuthenticationFailure(LoginFailureEvent $event): void
    {
        $this->logger->userLogin(-1, $this->requestStack->getCurrentRequest()->getClientIp(), $event->getRequest()->request->get('username'), false);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'security.authentication.success' => 'onSecurityAuthenticationSuccess',
            'Symfony\Component\Security\Http\Event\LoginFailureEvent' => 'onSecurityAuthenticationFailure',
        ];
    }

}
