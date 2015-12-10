<?php


namespace a15l\serialization\metadata\factory;


use a15l\serialization\metadata\loader\MetadataLoaderInterface;
use Doctrine\Common\Cache\Cache;

class CacheMetadataFactory extends AbstractFactory{

    private $cacheProvider;

    /**
     * @param \a15l\serialization\metadata\loader\MetadataLoaderInterface $loader
     * @param Cache $cacheProvider
     * @param array $defaultConfig
     */
    public function __construct(MetadataLoaderInterface $loader, Cache $cacheProvider, array $defaultConfig = array()){
        parent::__construct($loader, $defaultConfig);
        $this->cacheProvider = $cacheProvider;
    }


    /**
     * @param string $class fully qualified class name
     * @return array class configuration
     */
    public function getClassMetadata($class){
        $id = 'class-metadata::' . $class;
        if (!$this->cacheProvider->contains($id)) {
            $data = $this->getMetadata($class);
            $this->cacheProvider->save($id, $data);
        }
        return $this->cacheProvider->fetch($id);
    }
}