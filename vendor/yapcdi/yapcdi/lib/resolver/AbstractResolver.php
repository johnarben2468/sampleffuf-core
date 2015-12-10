<?php

namespace yapcdi\resolver;

/**
 * Description of AbstractResolver
 *
 * @author Alexander Schlegel
 */
abstract class AbstractResolver {

    public function __construct() {
        
    }

    public function getMethodParameters(\ReflectionMethod $method, array $aliases = array()) {
        $params = $method->getParameters();
        // We are done if the method has no parameters
        if (0 === count($params)) {
            return array();
        }
        $parameters = array();
        /* @var $param \ReflectionParameter */
        foreach ($params as $param) {
            $paramName = $param->getName();
            $parameters[$paramName] = array();
            if (isset($aliases[$paramName]) && array_key_exists('value', $aliases[$paramName])) {
                $parameters[$paramName]['value'] = $aliases[$paramName]['value'];
                continue;
            }
            $class = $param->getClass();
            if (null === $class) {
                if ($param->isOptional()) {
                    $parameters[$paramName]['value'] = $param->getDefaultValue();
                    continue;
                }
                $parameters[$paramName]['class'] = null;
                continue;
            }
            $parameters[$paramName]['class'] = $class->getName();
            if (isset($aliases[$paramName]['class'])) {
                $parameters[$paramName]['class'] = $aliases[$paramName]['class'];
            }
        }
        return $parameters;
    }

}
