<?php

namespace yapcdi\resolver;

use yapcdi\exception\AnnotationException;

/**
 * Description of MetadataResolver
 *
 * @author Alexander Schlegel
 */
class MetadataResolver extends AbstractResolver implements ResolverInterface {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 
     * @param \ReflectionMethod $method
     * @return array
     * @throws \LogicException
     */
    public function getMethodParameterAliases(\ReflectionMethod $method = null) {
        if ($method === null) {
            return array();
        }
        $aliases = array();
        // load all injection aliases defined with annotations
        preg_match_all("/@Inject\((.*?)\)/si", $method->getDocComment(), $m);
        if (count($m[0]) > 0) {
            foreach ($m[1] as $alias) {
                $alias = trim(str_replace(array('"', '(', ')'), '', $alias));
                if (strpos($alias, ',') === false) {
                    throw new AnnotationException("A required comma could not be found in the Inject annotation in: " . $method->class . '::' . $method->name);
                }
                $aliasInfo = explode(',', $alias);
                $value = trim($aliasInfo[1]);
                $aliases[trim($aliasInfo[0])] = array(
                    (substr($value, 0, 1) == '\\' ? 'class' : 'value') => $value
                );
            }
        }
        return $aliases;
    }

    public function getClassDependencies(\ReflectionClass $class) {
        $dependencies = array('constructor' => array());
        if (($constructor = $class->getConstructor()) !== null) {
            $aliases = $this->getMethodParameterAliases($constructor);
            $dependencies['constructor'] = parent::getMethodParameters($constructor, $aliases);
        }
        $dependencies['properties'] = $this->getInjectionProperties($class);
        $dependencies['setters'] = $this->getSetters($class);
        return $dependencies;
    }

    public function getInjectionProperties(\ReflectionClass $class) {
        $injections = array();
        $properties = $class->getProperties();
        /* @var $prop \ReflectionProperty */
        foreach ($properties as $prop) {
            preg_match("/@Inject(.*?)[\*\@]/si", $prop->getDocComment(), $m);
            if (count($m) > 0) {
                $dep = trim(str_replace(array('(', ')', '"'), '', $m[1]));
                if (strpos($dep, ',') !== false) {
                    throw new AnnotationException("Commatas are not allowed for class property inject annotations! " . $class->getName() . '#' . $prop->name);
                }
                if (strlen($dep) > 0) {
                    $injections[$prop->getName()] = array('class' => trim($dep));
                    continue;
                }
                preg_match("/@var\s(.*?)[\*\@\s]/si", $prop->getDocComment(), $ma);
                if (count($ma) > 0) {
                    $injections[$prop->getName()] = array('class' => trim($ma[1]));
                    continue;
                }
                throw new AnnotationException("If no @var annotation is provided, you have to specify the value or class for the property with the inject annotation! " . $class->getName() . '#' . $prop->name);
            }
        }
        return $injections;
    }

    public function getSetters(\ReflectionClass $class) {
        $setters = array();
        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if (stripos($method->getDocComment(), '@Inject') !== false &&
                    strtolower($method->getName()) !== '__construct') {
                $aliases = $this->getMethodParameterAliases($method);
                $setters[$method->getName()] = parent::getMethodParameters($method, $aliases);
            }
        }
        return $setters;
    }

}
