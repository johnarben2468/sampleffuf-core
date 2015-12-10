<?php

namespace yapcdi\controller\resolver\symfony;

use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use yapcdi\dic\DIContainerInterface;

/**
 * Description of ControllerResolver
 *
 * @author Alexander Schlegel
 */
class ConfigControllerResolver extends AbstractResolver {

    public function __construct(DIContainerInterface $diContainer, LoggerInterface $logger = null) {
        parent::__construct($diContainer, $logger);
    }

    public function getArguments(Request $request, $controller) {
        if (!is_array($controller)) {
            throw new \InvalidArgumentException('Can not resolve arguments '
            . 'for the controller type: "'
            . (($controller instanceof \Closure) ? 'closure' : gettype($controller))
            . '" for URI "' . $request->getPathInfo() . '"');
        }
        $attributes = $request->attributes->all();
        if (!isset($attributes['method.params'])) {
            return array();
        }
        $parameters = explode(',', preg_replace('/\s/s', '', $attributes['method.params']));
        $arguments = array();
        foreach ($parameters as $name) {
            if (array_key_exists($name, $attributes)) {
                $arguments[] = $attributes[$name];
                continue;
            }
            throw new \RuntimeException('Controller '
            . get_class($controller[0])
            . '::' . $controller[1] . ' requires that you provide a value '
            . 'for the "$' . $name
            . '" argument (because there is no default value or '
            . 'because there is a non optional argument after this one).');
        }
        return $arguments;
    }

}
