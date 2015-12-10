<?php


namespace a15l\serialization\metadata\loader\file;


class XMLFileLoader extends AbstractFileLoader{

    /**
     * AbstractFileLoader constructor.
     * @param string $configDir
     * @param string $suffix
     */
    public function __construct($configDir, $suffix = 'xml'){
        parent::__construct($configDir, $suffix);
    }

    protected function parseXMLData($file){
        $previous = libxml_use_internal_errors(true);
        $xml = simplexml_load_file($file);
        $xml->registerXPathNamespace('metadata', 'http://a15l.com/schemas/serialization/metadata');
        libxml_use_internal_errors($previous);
        $config = array();
        if (isset($xml->class['ignore-all'])) {
            $config['ignore-all'] = true;
        }
        if (isset($xml->class['default-deserialize-event'])) {
            $config['default-deserialize-event'] = (string)$xml->class['default-deserialize-event'];
        }
        if (isset($xml->class['default-serialize-event'])) {
            $config['default-serialize-event'] = (string)$xml->class['default-serialize-event'];
        }
        foreach ($xml->xpath('//metadata:class/metadata:readonly/metadata:property') as $node) {
            $config['readonly'][(string)$node['name']] = true;
        }

        foreach ($xml->xpath('//metadata:class/metadata:ignore/metadata:property') as $node) {
            $config['ignore'][(string)$node['name']] = true;
        }

        foreach ($xml->xpath('//metadata:class/metadata:aliases/metadata:alias') as $node) {
            $config['aliases'][(string)$node['property']] = (string)$node['name'];
        }

        foreach ($xml->xpath('//metadata:class/metadata:types/metadata:property') as $node) {
            $config['types'][(string)$node['name']][(string)$node['type']] = (string)$node['value'];
        }
        foreach ($xml->xpath('//metadata:class/metadata:events/metadata:event') as $node) {
            $config['events'][(string)$node['property']][(string)$node['type']] = (string)$node['name'];
        }
        return $config;
    }

    /**
     * @param string $file file name of the config file
     * @return array|null class configuration
     */
    public function getClassMetadataConfig($file){
        if (($absFile = $this->getAbsFileName($file)) === null) {
            return null;
        }
        return $this->parseXMLData($absFile);
    }
}