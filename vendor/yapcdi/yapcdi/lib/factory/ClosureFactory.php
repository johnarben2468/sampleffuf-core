<?php

namespace yapcdi\factory;

/**
 * Description of ClosureFactory
 *
 * @author Alexander Schlegel
 */
class ClosureFactory implements FactoryInterface {

    private $closures = array();
    private $shared = array();
    private $instances = array();

    public function __construct() {
        
    }

    public function create($component) {
        if (isset($this->closures[$component])) {
            return call_user_func($this->closures[$component]);
        }
        if (isset($this->shared[$component])) {
            if (!isset($this->instances[$component])) {
                $this->instances[$component] = call_user_func($this->shared[$component]);
            }
            return $this->instances[$component];
        }
        throw new \LogicException("Closure for component $component not found");
    }

    public function add($component, \Closure $closure) {
        $this->closures[$component] = $closure;
        return $this;
    }

    public function addShared($component, \Closure $closure) {
        $this->shared[$component] = $closure;
        return $this;
    }

}
