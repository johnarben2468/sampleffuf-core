<?php


namespace a15l\serialization\metadata\factory;


use a15l\serialization\metadata\loader\MetadataLoaderInterface;

class MetadataFactory extends AbstractFactory{

    /**
     * @var array
     */
    private $arrayCache = array();

    /**
     * @param \a15l\serialization\metadata\loader\MetadataLoaderInterface $loader
     * @param array $defaultConfig
     */
    public function __construct(MetadataLoaderInterface $loader, array $defaultConfig = array()){
        parent::__construct($loader, $defaultConfig);
    }

    /**
     * @param string $class fully qualified class name
     * @return array class configuration
     */
    public function getClassMetadata($class){
        if (!isset($this->arrayCache[$class])) {
            $this->arrayCache[$class] = $this->getMetadata($class);
        }
        return $this->arrayCache[$class];
    }
}