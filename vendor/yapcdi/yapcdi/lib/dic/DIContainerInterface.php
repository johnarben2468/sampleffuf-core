<?php

namespace yapcdi\dic;

/**
 *
 * @author Alexander Schlegel
 */
interface DIContainerInterface {

    /**
     * 
     * @param string $source
     * @param string $target
     * @return DIContainerInterface
     */
    public function addAlias($source, $target);

    /**
     * Add a singleton definition to the container
     * 
     * @param object|\Closure $instance
     * @param string $classAlias
     * @return DIContainerInterface
     */
    public function addSharedInstance($instance, $classAlias = null);

    /**
     * 
     * @param string $parameterName
     * @param mixed $value
     * @return DIContainerInterface
     */
    public function addSharedParameter($parameterName, $value);

    /**
     * 
     * @param type $class
     * @param type $method
     * @param array $parameters key = parameter name, value = parameter value
     * @return DIContainerInterface
     */
    public function addSharedClassParameters($class, $method, array $parameters);

    /**
     * 
     * @param string $class
     * @return object
     */
    public function make($class);

    /**
     * 
     * @param string $class
     * @return DIContainerInterface
     */
    public function reset($class = null);
}
