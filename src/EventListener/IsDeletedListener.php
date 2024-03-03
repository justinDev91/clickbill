<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class IsDeletedListener
{
    private $tokenStorage;
    private $urlGenerator;

    public function __construct(TokenStorageInterface $tokenStorage, UrlGeneratorInterface $urlGenerator)
    {
        $this->tokenStorage = $tokenStorage;
        $this->urlGenerator = $urlGenerator;
    }

    #[AsEventListener(event: 'security.interactive_login')]
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        // Récupérer le jeton d'authentification
        $token = $this->tokenStorage->getToken();

        if ($token === null) {
            return;
        }

        // Récupérer l'utilisateur connecté à partir du jeton
        $user = $token->getUser();

        // Vérifier si l'utilisateur est marqué comme supprimé
        if ($user->isIsDeleted() || $user->getCompany()->isIsDeleted()) {
            // Déconnecter l'utilisateur
            $this->tokenStorage->setToken(null);
            $loginUrl = $this->urlGenerator->generate('app_login', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $response = new RedirectResponse($loginUrl);
            $response->send();
        }
    }
}