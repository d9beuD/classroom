<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

final class AuthenticationFlashListener
{
    // Also fires for the automatic login after registration.
    #[AsEventListener]
    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $this->flash($event->getRequest(), 'Bienvenue, vous êtes connecté.');
    }

    // Below SessionLogoutListener (priority 0), so the flash lands in the fresh session.
    #[AsEventListener(priority: -10)]
    public function onLogout(LogoutEvent $event): void
    {
        $this->flash($event->getRequest(), 'Vous êtes déconnecté.');
    }

    private function flash(Request $request, string $message): void
    {
        $session = $request->hasSession() ? $request->getSession() : null;
        if ($session instanceof FlashBagAwareSessionInterface) {
            $session->getFlashBag()->add('success', $message);
        }
    }
}
