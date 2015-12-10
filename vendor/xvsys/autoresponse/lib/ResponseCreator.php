<?php

namespace xvsys\autoresponse;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use xvsys\validator\UserInputException;
use xvsys\validator\ValidationException;

/**
 * Description of ResponseCreator
 *
 * @author Alexander Schlegel
 */
class ResponseCreator implements EventSubscriberInterface {

    public function __construct() {
        
    }

    public function onKernelView(GetResponseForControllerResultEvent $e) {
        $result = $e->getControllerResult();
        if ($e->getRequest()->isXmlHttpRequest()) {
            $response = new JsonResponse(array(
                'status' => 200, 'errors' => array(), 'location' => null,
                'content' => $result
            ));
        } else {
            $response = new Response($result);
        }
        $e->setResponse($response);
    }

    public function onKernelResponse(FilterResponseEvent $e) {
        $response = $e->getResponse();
        $request = $e->getRequest();
        if ($response instanceof RedirectResponse) {
            $uri = $response->getTargetUrl();
            if (strpos($uri, '://') !== false) {
                $location = $uri;
            } else {
                $location = $request->getUriForPath($uri);
            }
            if ($request->isXmlHttpRequest()) {
                $jsonResponse = new JsonResponse(array(
                    'status' => $response->getStatusCode(), 'errors' => [],
                    'location' => $location, 'content' => ''
                        ), 200);
                $e->setResponse($jsonResponse);
            } else {
                $response->setTargetUrl($location);
                $e->setResponse($response);
            }
        }
    }

    public function onKernelException(GetResponseForExceptionEvent $e) {
        $ex = $e->getException();
        $request = $e->getRequest();
        if ($ex instanceof RedirectException) {
            $uri = $ex->getUrl();
            if (strpos($uri, '://') !== false) {
                $location = $uri;
            } else {
                $location = $request->getUriForPath($uri);
            }
            if ($request->isXmlHttpRequest()) {
                $response = new JsonResponse(array(
                    'status' => 301, 'errors' => [], 'location' => $location,
                    'content' => ''
                ));
            } else {
                $response = new Response('', $ex->getStatus(), array(
                    'location' => $location
                ));
            }
            $e->setResponse($response);
            return;
        }
        if ($request->isXmlHttpRequest()) {
            $errorMsgs = array();
            if ($ex instanceof UserInputException) {
                $errorMsgs[] = $ex->getMessage();
            }
            if ($ex instanceof ValidationException) {
                $violations = $ex->getViolations();
                foreach ($violations as $violation) {
                    $errorMsgs[] = $violation->getMessage();
                }
            }
            if (count($errorMsgs) > 0) {
                $response = new JsonResponse(array(
                    'status' => 400, 'errors' => $errorMsgs,
                    'location' => null, 'content' => ''
                        ), 400);
                $e->setResponse($response);
            }
            return;
        }
    }

    public static function getSubscribedEvents() {
        return array(
            KernelEvents::VIEW => array(array('onKernelView', 10)),
            KernelEvents::RESPONSE => array(array('onKernelResponse', 10)),
            KernelEvents::EXCEPTION => array(array('onKernelException', 10))
        );
    }

}
