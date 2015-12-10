<?php

namespace yapcdi\resolver;

/**
 * Description of ConfigResolver
 *
 * @author Alexander Schlegel
 */
class ConfigResolver extends AbstractResolver implements ResolverInterface {

    private $config = array();

    public function __construct() {
        parent::__construct();
    }

    public function setConfig(array $config) {
        $this->config = $config;
    }

    public function getClassDependencies(\ReflectionClass $class) {
        $dependencies = array(
            'constructor' => array(),
            'properties' => array(),
            'setters' => array()
        );
        $className = $class->getName();
        if (($constructor = $class->getConstructor()) !== null) {
            $aliases = array();
            if (isset($this->config[$className]['constructor'])) {
                $aliases = $this->config[$className]['constructor'];
            }
            $dependencies['constructor'] = parent::getMethodParameters($constructor, $aliases);
        }
        if (isset($this->config[$className]['properties'])) {
            $dependencies['properties'] = $this->config[$className]['properties'];
        }
        if (isset($this->config[$className]['setters'])) {
            $dependencies['setters'] = $this->config[$className]['setters'];
        }
        return $dependencies;
    }

}
