<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class MicrosoftController extends AbstractController
{
    #[Route('/connect/microsoft', name: 'connect_microsoft')]
    public function connectAction(ClientRegistry $clientRegistry)
    {
        return $clientRegistry
            ->getClient('microsoft')
            ->redirect([
                'public_profile' => 'email'
            ]);
    }

    #[Route('/connect/microsoft/check', name: 'connect_microsoft_check')]
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry)
    {
        $client = $clientRegistry->getClient('microsoft');

        try {
            $accessToken = $client->getAccessToken();
            $user = $client->fetchUserFromToken($accessToken);
            $provider = $client->getOAuth2Provider();

            // @todo 1) Check if user exists in database
        } catch (IdentityProviderException $e) {
            dd($e->getMessage());
        }
    }
}
