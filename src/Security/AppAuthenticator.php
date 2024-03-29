<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;

class AppAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private Session $session;

    public function __construct(private UrlGeneratorInterface $urlGenerator, private TokenStorageInterface $tokenStorage)
    {
        $this->session = new Session();
    }

    public function authenticate(Request $request): Passport
    {
        $username = $request->request->get('username', '');

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $username);

        return new Passport(
            new UserBadge($username),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {

        // Check if user is enabled/pending in database
        // @todo These flash messages will not display as we are redirecting to /logout and /logout is redirecting to /
        $roles = $token->getUser()->getRoles();
        if (!($token->getUser()->isEnabled()) || ($token->getUser()->isPending()) || ((in_array('ROLE_DENY_LOGIN', $roles)) && !(in_array(['ROLE_SUPER_ADMIN', 'ROLE_ADMIN'], $roles)))) {
                // destroy session
                $this->tokenStorage->setToken(null);
                $request->getSession()->invalidate();

                $this->session->getFlashBag()->add('warning', 'User account is either disabled or pending admin approval.');
                return new RedirectResponse('/login');
        }

        if (!(is_null($token->getUser()->getGoogleId()))) {
            // destroy session
            $this->tokenStorage->setToken(null);
            $request->getSession()->invalidate();

            $this->session->getFlashBag()->add('warning', 'User account is authenticated with a different method. Please login with that method.');
            return new RedirectResponse('/');
        }

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('app_user_profile'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
