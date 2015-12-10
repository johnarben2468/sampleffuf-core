<?php

namespace xvsys\csrf;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Description of CSRFTokenValidator
 *
 * @author Alexander Schlegel
 */
class CSRFTokenValidator implements EventSubscriberInterface {

    private $token;

    public function __construct($token) {
        $this->token = $token;
    }

    public function onKernelRequest(GetResponseEvent $e) {
        $r = $e->getRequest();
        if ($r->isMethod('POST') && ($this->token != $r->headers->get('csrf-token') && $this->token != $r->request->get('csrf-token') )) {
            throw new CSRFException("CSRF-Attack detected");
        }
    }

    public static function getSubscribedEvents() {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 1000))
        );
    }

}
