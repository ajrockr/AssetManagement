<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\SiteConfig;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class GoogleAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{
    use BearerAuthorizationTrait;

    private ClientRegistry $clientRegistry;
    private EntityManagerInterface $entityManager;
    private RouterInterface $router;
    private EventDispatcherInterface $eventDispatcher;
    private TokenStorageInterface $tokenStorage;
    private Session $session;

    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $entityManager, RouterInterface $router, EventDispatcherInterface $eventDispatcher, TokenStorageInterface $tokenStorage)
    {
        $this->clientRegistry = $clientRegistry;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;
        $this->session = new Session();

        // Check if Google authentication was disabled
        if ($this->entityManager->getRepository(SiteConfig::class)->findOneByName('auth_useGoogle')->getConfigValue() !== "1") {
            $this->session->getFlashBag()->add('notice', 'Use of Google authentication is disabled.');
            return new RedirectResponse('/');
        }
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function() use ($accessToken, $client) {
                $googleUser = $client->fetchUserFromToken($accessToken);
                $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['googleId' => $googleUser->getId()]);

                $email = $googleUser->toArray()['email'];
                $picture = $googleUser->toArray()['picture'];

                $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
                if ($user) {
                    $user->setAvatar($picture);

                    if (!($user->getGoogleId())) {
                        $user->setGoogleId($googleUser->getId());
                    }

                    $this->entityManager->persist($user);
                    $this->entityManager->flush();
                } else {
                    if ($this->entityManager->getRepository(SiteConfig::class)->findOneByName('user_allowRegistration')->getConfigValue() == "1") {
                        $user = new User();
                        $user->setEmail($email)
                            ->setUsername($email)
                            ->setEnabled(false)
                            ->setPending(true)
                            ->setFirstname($googleUser->getFirstName())
                            ->setSurname($googleUser->getLastName())
                            ->setGoogleId($googleUser->getId())
                            ->setAvatar($picture)
                            // @todo Will setting the password to null cause any harm?
                        ;

                        $this->entityManager->persist($user);
                        $this->entityManager->flush();
                    }
                }

                return $user;
            }),
            [
                (new RememberMeBadge())->enable()
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $token->getUser()->getEmail()]);

        // User is pending, deny
        if (null !== $user) {
            if (($user->isPending())) {
                // Destroy user session
                $this->tokenStorage->setToken(null);
                $request->getSession()->invalidate();

                $this->session->getFlashBag()->add('warning', 'Your user account is now pending administrative approval.');
                return new RedirectResponse('/');
            } elseif (!($user->isEnabled())) {
                // Destroy user session
                $this->tokenStorage->setToken(null);
                $request->getSession()->invalidate();

                $this->session->getFlashBag()->add('warning', 'Your user account is disabled or pending administrative approval.');
                return new RedirectResponse('/');
            } elseif (in_array('ROLE_DENY_LOGIN', $user->getRoles())) {
                // Destroy user session
                $this->tokenStorage->setToken(null);
                $request->getSession()->invalidate();

                $this->session->getFlashBag()->add('warning', 'Your user account is not permitted to login.');
                return new RedirectResponse('/');
            }
        }

        $targetUrl = $this->router->generate('app_home');

        return new RedirectResponse($targetUrl);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());
        $this->session->getFlashBag()->add('warning', $message);

        // return new Response($message, Response::HTTP_FORBIDDEN);
        return new RedirectResponse('/');
    }

   public function start(Request $request, AuthenticationException $authException = null): Response
   {
       /*
        * If you would like this class to control what happens when an anonymous user accesses a
        * protected page (e.g. redirect to /login), uncomment this method and make this class
        * implement Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface.
        *
        * For more details, see https://symfony.com/doc/current/security/experimental_authenticators.html#configuring-the-authentication-entry-point
        */
        return new RedirectResponse(
            '/connect/',
            Response::HTTP_TEMPORARY_REDIRECT
        );
   }

   public function supportsRememberMe(): bool
   {
       return true;
   }
}
