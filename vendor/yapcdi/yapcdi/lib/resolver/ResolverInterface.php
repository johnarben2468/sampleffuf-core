<?php

namespace yapcdi\resolver;

/**
 *
 * @author Alexander Schlegel
 */
interface ResolverInterface {

    /**
     * 
     * @param \ReflectionClass $class
     * @return array Returns an ssociative array 
     * <pre>
     * [
     *    "constructor" => [
     *      "Paramtername" => 
     *    ]
     * ]
     * </pre>
     */
    public function getClassDependencies(\ReflectionClass $class);
}
