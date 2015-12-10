<?php

namespace yapcdi\controller\resolver\symfony;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use yapcdi\dic\DIContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Description of AbstractResolver
 *
 * @author Alexander Schlegel
 */
abstract class AbstractResolver implements ControllerResolverInterface {

    /**
     *
     * @var DIContainerInterface 
     */
    protected $diContainer;

    /**
     *
     * @var LoggerInterface 
     */
    protected $logger;

    public function __construct(DIContainerInterface $diContainer, LoggerInterface $logger = null) {
        $this->diContainer = $diContainer;
        $this->logger = $logger;
    }

    protected function createControllerInstance($controller, Request $request) {
        if (false === is_string($controller) || false === strpos($controller, '::')) {
            throw new \InvalidArgumentException('Unable to find controller "' . $controller . '".');
        }
        list($class, $method) = explode('::', $controller, 2);
        if (!class_exists($class)) {
            throw new \InvalidArgumentException('Class "' . $class . '" does not exist.');
        }
        $inst = $this->diContainer->make($class);
        if (!is_callable(($callable = array($inst, $method)))) {
            throw new \InvalidArgumentException('Controller "'
            . $controller
            . '" for URI "' . $request->getPathInfo() . '" is not callable.');
        }
        return $callable;
    }

    public function getController(Request $request) {
        if (!$controller = $request->attributes->get('_controller')) {
            if (null !== $this->logger) {
                $this->logger->warning('Unable to look for the controller as the "_controller" parameter is missing');
            }
            return false;
        }
        return $this->createControllerInstance($controller, $request);
    }

}
