<?php

namespace abd\app\core;

use Monolog\ErrorHandler;
use phastl\ViewEngineInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionHandler implements EventSubscriberInterface {

    /**
     * @var ViewEngineInterface
     */
    private $view;

    /**
     * @var ErrorHandler
     */
    private $logger;

    /**
     *
     * @var boolean
     */
    private $debugMode;

    public function __construct(ViewEngineInterface $view, ErrorHandler $logger, $debugMode = false) {
        $this->view = $view;
        $this->logger = $logger;
        $this->debugMode = $debugMode;
    }

    public function onKernelException(GetResponseForExceptionEvent $e) {
        $ex = $e->getException();
        $response = new Response();
        if ($ex instanceof NotFoundHttpException) {
            $response->setContent($this->view->render('error/404'));
            $e->setResponse($response);
            return;
        }
        if ($this->debugMode) {
            \Symfony\Component\Debug\ExceptionHandler::register();
            throw $ex;
        }
        $response->setContent($this->view->render('error/exception'));
        $e->setResponse($response);
        $this->logger->handleException($ex);
    }

    public static function getSubscribedEvents() {
        return array(
            KernelEvents::EXCEPTION => array(array('onKernelException', 1))
        );
    }

}
