<?php


namespace a15l\serialization\deserializer;


interface DeserializerInterface{

    /**
     *
     * @param string $data
     * @param string|null $targetClass
     * @return array|object
     */
    public function deserialize($data, $targetClass);

    /**
     * @param array|callable $filter
     * @return DeserializerInterface
     */
    public function setFilter($filter);
}