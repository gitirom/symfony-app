<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class LogoutSubscriber implements EventSubscriberInterface
{
    private $urlGenerator;
    private $requestStack;

    public function __construct(UrlGeneratorInterface $urlGenerator, RequestStack $requestStack)
    {
        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents(): array
    {
        return [LogoutEvent::class => 'onLogout'];
    }

    public function onLogout(LogoutEvent $event): void
    {
        $this->requestStack->getSession()->getFlashBag()->add('info', 'You have been successfully logged out.');
        $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_home')));
    }
}

