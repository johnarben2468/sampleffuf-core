<?php


namespace a15l\serialization\deserializer;

use a15l\serialization\events\EventDispatcherInterface;
use a15l\serialization\metadata\factory\MetadataFactoryInterface;

class HTTPQueryStringDeserializer extends AbstractDeserializer{

    /**
     * AbstractSerializer constructor.
     * @param EventDispatcherInterface $dispatcher
     * @param MetadataFactoryInterface $metadataFactory
     */
    public function __construct(EventDispatcherInterface $dispatcher, MetadataFactoryInterface $metadataFactory){
        parent::__construct($dispatcher, $metadataFactory);
    }

    /**
     *
     * @param string $data
     * @param string|null $targetClass
     * @return array|object
     */
    public function deserialize($data, $targetClass){
        $array = array();
        parse_str($data, $array);
        if (is_int(key($array))) {
            $data = array();
            foreach ($array as $k => $v) {
                $data[$k] = $this->doDeserialize($v, $targetClass);
            }
            return $data;
        }
        return $this->doDeserialize($array, $targetClass);
    }
}