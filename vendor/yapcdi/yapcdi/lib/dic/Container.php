<?php

namespace yapcdi\dic;

use yapcdi\resolver\ResolverInterface;
use yapcdi\exception\CircularDependencyException;
use yapcdi\exception\InjectionException;

/**
 * Description of Container
 *
 * @author Alexander Schlegel
 */
class Container implements DIContainerInterface {

    /**
     *
     * @var ResolverInterface 
     */
    protected $resolver;

    /**
     * HashMap 
     * key => Class 
     * value => instance
     * @var array 
     */
    protected $sharedInstances = array();

    /**
     * HashMap 
     * key => name of the global parameter 
     * value => value of the parameter
     * @var array 
     */
    protected $sharedParameters = array();

    /**
     * HashMap 
     * key => name of the source class 
     * value => name of the target class 
     * @var array 
     */
    protected $aliases = array();

    /**
     * HashMap 
     * key 
     *   => name of the source class 
     * value 
     *   => HashMap 
     *      key 
     *        => method name 
     *      value 
     *        => HashMap n
     *           key 
     *             => parameter name
     *           value 
     *             => parameter value
     * @var array
     */
    protected $sharedClassParameters = array();

    /**
     *
     * @var array 
     */
    protected $closureCache = array();

    /**
     *
     * @var array 
     */
    protected $classInfoCache = array();

    /**
     *
     * @var array 
     */
    protected $resolving = array();

    public function __construct(ResolverInterface $resolver) {
        $this->resolver = $resolver;
    }

    public function addAlias($source, $target) {
        $this->aliases[ltrim($source, '\\')] = ltrim($target, '\\');
        return $this;
    }

    public function addSharedInstance($instance, $classAlias = null) {
        if (null === $classAlias && !is_callable($instance)) {
            $this->sharedInstances[ltrim(get_class($instance), '\\')] = $instance;
            return $this;
        }
        $this->sharedInstances[ltrim($classAlias, '\\')] = $instance;
        return $this;
    }

    public function addSharedParameter($parameterName, $value) {
        $this->sharedParameters[$parameterName] = $value;
        return $this;
    }

    protected function getClassInfoFromCache(\ReflectionClass $refClass) {
        $className = $refClass->getName();
        if (!isset($this->classInfoCache[$className])) {
            $this->classInfoCache[$className] = $this->resolver->getClassDependencies($refClass);
        }
        return $this->classInfoCache[$className];
    }

    public function make($class) {
        $className = ltrim($class, '\\');
        if (isset($this->aliases[$className])) {
            $className = $this->aliases[$className];
        }
        if (isset($this->sharedInstances[$className])) {
            return $this->getInstance($this->sharedInstances[$className], $className);
        }
        if (isset($this->resolving[$className])) {
            throw new CircularDependencyException($className);
        }
        $this->resolving[$className] = true;

        if (!isset($this->closureCache[$className])) {
            $refClass = new \ReflectionClass($className);
            $constructor = $refClass->getConstructor();
            $classInfo = $this->getClassInfoFromCache($refClass);
            $params = $constructor ? $this->resolveMethodParameters($classInfo['constructor'], $className, '__construct') : null;
            $setters = array();
            $properties = $classInfo['properties'];
            if (!$refClass->isInstantiable()) {
                $this->resolving = array();
                throw new InjectionException("Class/Interface " . $className . ' is not instantiable!', InjectionException::CLASS_NOT_INSTANTIABLE);
            }
            foreach ($classInfo['setters'] as $setterName => $sParams) {
                $setters[$setterName] = $this->resolveMethodParameters($sParams, $className, $setterName);
            }
            $this->closureCache[$className] = function() use ($refClass, $params, $setters, $properties) {
                $instance = $params ? $refClass->newInstanceArgs($params()) : new $refClass->name;
                foreach ($setters as $setterName => $sParams) {
                    call_user_func_array(array($instance, $setterName), $sParams());
                }
                foreach ($properties as $propName => $propInfo) {
                    $prop = $refClass->getProperty($propName);
                    if (($accessible = $prop->isPublic()) === false) {
                        $prop->setAccessible(true);
                    }
                    if (isset($propInfo['class'])) {
                        $propValue = $this->make($propInfo['class']);
                    } else {
                        $propValue = isset($propInfo['value']) ? $propInfo['value'] : null;
                    }
                    $prop->setValue($instance, $propValue);
                    if ($accessible === false) {
                        $prop->setAccessible(false);
                    }
                }
                return $instance;
            };
        }
        $inst = $this->closureCache[$className]();
        unset($this->resolving[$className]);
        return $inst;
    }

    protected function resolveMethodParameters(array $parameters, $class, $method) {
        $paramCache = $toResolveIndexes = array();
        $i = 0;
        foreach ($parameters as $paramName => $pInfo) {
            if (isset($this->sharedClassParameters[$class][$method]) && array_key_exists($paramName, $this->sharedClassParameters[$class][$method])) {
                $paramCache[$i++] = $this->sharedClassParameters[$class][$method][$paramName];
                continue;
            }
            if (array_key_exists($paramName, $this->sharedParameters)) {
                $paramCache[$i++] = $this->getInstance($this->sharedParameters[$paramName], $paramName);
                continue;
            }
            if (array_key_exists('value', $pInfo)) {
                $paramCache[$i++] = $this->getInstance($pInfo['value'], $paramName);
                continue;
            }
            if (!isset($pInfo['class']) || null === $pInfo['class']) {
                $this->resolving = array();
                throw new InjectionException('No value for param ' . $paramName, InjectionException::MISSING_REQUIRED_PARAM);
            }
            $className = $pInfo['class'];
            if (isset($this->aliases[$className])) {
                $className = $this->aliases[$className];
            }
            if (isset($this->sharedInstances[$className])) {
                $paramCache[$i++] = $this->getInstance($this->sharedInstances[$className], $className);
                continue;
            }
            $paramCache[$i] = $className;
            $toResolveIndexes[$i++] = $className;
        }

        return function() use ($paramCache, $toResolveIndexes) {
            foreach ($toResolveIndexes as $i => $className) {
                $paramCache[$i] = $this->make($className);
            }
            return $paramCache;
        };
    }

    protected function getInstance($class, $className) {
        if ($class instanceof \yapcdi\factory\FactoryInterface) {
            return $class->create($className);
        }
        if (is_callable($class)) {
            return call_user_func($class);
        }
        return $class;
    }

    public function setConfig(array $config) {
        foreach ($config as $key => $value) {
            switch (strtolower($key)) {
                case 'aliases':
                    foreach ($value as $source => $target) {
                        $this->addAlias($source, $target);
                    }
                    break;
                case 'sharedinstances':
                    foreach ($value as $alias => $instance) {
                        $this->addSharedInstance($instance, $alias);
                    }
                    break;
                case 'sharedparameters':
                    foreach ($value as $param => $pval) {
                        $this->addSharedParameter($param, $pval);
                    }
                    break;
                case 'sharedclassparameters':
                    foreach ($value as $class => $methods) {
                        foreach ($methods as $method => $params) {
                            $this->addSharedClassParameters($class, $method, $params);
                        }
                    }
                    break;
            }
        }

        return $this->reset();
    }

    public function reset($class = null) {
        if (null === $class) {
            $this->closureCache = $this->classInfoCache = array();
            return $this;
        }
        $className = ltrim($class, '\\');
        if (isset($this->closureCache[$className])) {
            unset($this->closureCache[$className]);
        }
        if (isset($this->classInfoCache[$className])) {
            unset($this->classInfoCache[$className]);
        }
        return $this;
    }

    public function addSharedClassParameters($class, $method, array $parameters) {
        $className = ltrim($class, '\\');
        $this->sharedClassParameters[$className][$method] = $parameters;
        return $this;
    }

}
