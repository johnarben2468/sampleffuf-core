<?php


namespace a15l\serialization\metadata\loader\file;


use a15l\serialization\metadata\loader\MetadataLoaderInterface;

abstract class AbstractFileLoader implements FileLoaderInterface{

    /**
     * @var string
     */
    protected $configDir;
    /**
     * @var string
     */
    protected $suffix;

    /**
     * AbstractFileLoader constructor.
     * @param string $configDir
     * @param string $suffix
     */
    public function __construct($configDir, $suffix){
        $this->configDir = $configDir;
        $this->suffix = $suffix;
    }

    protected function getAbsFileName($fileName){
        $file = $this->configDir . DIRECTORY_SEPARATOR . $fileName . '.' . $this->suffix;
        if (file_exists($file) === true) {
            return $file;
        }
        return null;
    }

}