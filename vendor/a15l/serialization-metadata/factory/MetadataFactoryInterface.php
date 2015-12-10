<?php


namespace a15l\serialization\metadata\factory;


interface MetadataFactoryInterface{

    /**
     * @param string $class fully qualified class name
     * @return array class configuration
     */
    public function getClassMetadata($class);
}