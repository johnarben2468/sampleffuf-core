<?php


namespace a15l\serialization\metadata\loader\file;


class JSONLoader extends AbstractFileLoader{

    /**
     * AbstractFileLoader constructor.
     * @param string $configDir
     * @param string $suffix
     */
    public function __construct($configDir, $suffix = 'json'){
        parent::__construct($configDir, $suffix);
    }


    /**
     * @param string $file file name of the config file
     * @return array|null class configuration
     */
    public function getClassMetadataConfig($file){
        if (($absFile = $this->getAbsFileName($file)) === null) {
            return null;
        }
        return json_decode(file_get_contents($absFile), true);
    }
}