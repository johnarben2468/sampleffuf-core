<?php


namespace a15l\serialization\metadata\loader;


use a15l\serialization\metadata\loader\file\FileLoaderInterface;

class LazyMetadataLoader implements MetadataLoaderInterface{

    /**
     * @var FileLoaderInterface
     */
    private $fileLoader;

    /**
     * LazyMetadataLoader constructor.
     * @param FileLoaderInterface $fileLoader
     */
    public function __construct(FileLoaderInterface $fileLoader){
        $this->fileLoader = $fileLoader;
    }

    /**
     * @param string $class fully qualified class name
     * @return array|null class configuration
     */
    public function getMetadata($class){
        $file = str_replace('\\', '.', trim($class, '\\'));
        return $this->fileLoader->getClassMetadataConfig($file);
    }
}