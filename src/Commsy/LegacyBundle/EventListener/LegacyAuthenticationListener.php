<?php

namespace Commsy\LegacyBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Commsy\LegacyBundle\Authentication\LegacyAuthentication;
use Commsy\LegacyBundle\Services\LegacyEnvironment;

/**
 * Class LegacyAuthenticationListener
 *
 * @package EventListener;
 */
class LegacyAuthenticationListener implements EventSubscriberInterface
{
    private $legacyAuthentication;
    private $legacyEnvironment;

    public function __construct(LegacyAuthentication $legacyAuthentication, LegacyEnvironment $legacyEnvironment)
    {
        $this->legacyAuthentication = $legacyAuthentication;
        $this->legacyEnvironment = $legacyEnvironment->getEnvironment();
    }

    /**
     * {@inheritDocs}
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onKernelRequest', 400),
        );
    }

    /**
     * @param  GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $isAuthenticated = $this->legacyAuthentication->authenticate();

        // if not authenticated by the legacy code, redirect back to portal
        if (!$isAuthenticated) {
            $portalId = $this->legacyEnvironment->getCurrentPortalItem()->getItemID();
            $url = $event->getRequest()->getBaseUrl() . '?cid=' . $portalId;
            $response = new RedirectResponse($url);
            $event->setResponse($response);
        }
    }
}