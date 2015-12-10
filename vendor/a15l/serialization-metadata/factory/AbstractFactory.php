<?php


namespace a15l\serialization\metadata\factory;


use a15l\serialization\metadata\loader\MetadataLoaderInterface;

abstract class AbstractFactory implements MetadataFactoryInterface{

    /**
     * @var \a15l\serialization\metadata\loader\MetadataLoaderInterface
     */
    protected $loader;
    /**
     * @var array
     */
    protected $defaultConfig = array();

    /**
     * @param \a15l\serialization\metadata\loader\MetadataLoaderInterface $loader
     * @param array $defaultConfig
     */
    public function __construct(MetadataLoaderInterface $loader, array $defaultConfig = array()){
        $this->loader = $loader;
        $this->defaultConfig = $defaultConfig;
    }

    protected function getMetadata($class){
        $defaultConfig = $this->defaultConfig;
        $classCfg = $this->loader->getMetadata($class);
        // check if ignore all flag is set by default and class configuration was returned
        if (isset($defaultConfig['ignore-all']) && $classCfg !== null && !isset($classCfg['ignore-all'])) {
            // remove the default flag
            unset($defaultConfig['ignore-all']);
        }
        if ($classCfg === null) {
            return $defaultConfig;
        }
        // override default config values with class config values
        foreach ($classCfg as $k => $v) {
            $defaultConfig[$k] = $v;
        }
        return $defaultConfig;
    }

}