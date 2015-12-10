<?php


namespace a15l\serialization\metadata\loader;


interface MetadataLoaderInterface{

    /**
     * @param string $class fully qualified class name
     * @return array|null class configuration
     */
    public function getMetadata($class);

}