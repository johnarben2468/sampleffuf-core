<?php


namespace a15l\serialization\metadata\loader\file;


interface FileLoaderInterface{

    /**
     * @param string $file file name of the config file
     * @return array|null class configuration
     */
    public function getClassMetadataConfig($file);

}