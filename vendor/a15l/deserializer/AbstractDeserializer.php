<?php


namespace a15l\serialization\deserializer;


use a15l\serialization\events\EventDispatcherInterface;
use a15l\serialization\metadata\factory\MetadataFactoryInterface;

abstract class AbstractDeserializer implements DeserializerInterface{

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var MetadataFactoryInterface
     */
    protected $metadataFactory;

    /**
     * @var array|callable
     */
    protected $filter;

    /**
     * AbstractSerializer constructor.
     * @param EventDispatcherInterface $dispatcher
     * @param MetadataFactoryInterface $metadataFactory
     */
    public function __construct(EventDispatcherInterface $dispatcher, MetadataFactoryInterface $metadataFactory){
        $this->dispatcher = $dispatcher;
        $this->metadataFactory = $metadataFactory;
    }

    protected function doDeserialize(array $data, $targetClass){
        $refClass = new \ReflectionClass($targetClass);
        $class = $targetClass;
        if ($refClass->getConstructor() !== null && count($refClass->getConstructor()->getParameters()) === 0) {
            $instance = $refClass->newInstance();
        } else {
            $instance = $refClass->newInstanceWithoutConstructor();
        }
        $metadata = $this->metadataFactory->getClassMetadata($class);
        // ignore all properties?
        if (isset($metadata['ignore-all'])) {
            // we're done, nothing to deserialize
            return $instance;
        }
        $properties = $refClass->getProperties();
        /** @var $property \ReflectionProperty */
        foreach ($properties as $property) {
            if (isset($metadata['ignore'][$property->name]) || isset($metadata['readonly'][$property->name])) {
                continue;
            }
            $name = isset($metadata['aliases'][$property->name]) ? $metadata['aliases'][$property->name] : $property->name;
            if (!array_key_exists($name, $data)) {
                continue;
            }
            $value = $data[$name];
            // apply filter if one is set
            if ($this->filter !== null) {
                $value = call_user_func_array($this->filter, array($value));
            }
            // call default event if one is set
            $eventName = isset($metadata['default-deserialize-event']) ? $metadata['default-deserialize-event'] : null;
            if ($eventName !== null) {
                $value = $this->dispatcher->dispatch(EventDispatcherInterface::EVENT_DESERIALIZE, $eventName, $value);
            }
            if (($accessible = $property->isPublic()) === false) {
                $property->setAccessible(true);
            }
            $property->setValue($instance, $this->convertValue($property->name, $value, $metadata));
            if (!$accessible) {
                $property->setAccessible($accessible);
            }
        }
        return $instance;
    }

    protected function convertValue($property, $value, $mdt){
        // check if a event was defined
        if (isset($mdt['events'][$property]['deserialize'])) {
            $eventName = $mdt['events'][$property]['deserialize'];
            if ($this->dispatcher->hasSubscriber(EventDispatcherInterface::EVENT_DESERIALIZE, $eventName)) {
                $value = $this->dispatcher->dispatch(EventDispatcherInterface::EVENT_DESERIALIZE, $eventName, $value);
            }
        }
        $type = !isset($mdt['types'][$property]) ? '' : key($mdt['types'][$property]);

        // if data type should be an array and the input is not an array, create an empty array
        if (($type == 'object-array' || $type == 'array' || $type == 'scalar-array' || $type == 'array-collection')
            && is_array($value) === false
        ) {
            $value = array();
        }

        switch ($type) {
            case 'array':
                // will not be casted
                return $value;
            case 'scalar-array';
                // cast all array elements to the specified type
                $data = array();
                $castType = $mdt['types'][$property]['scalar-array'];
                foreach ($value as $k => $v) {
                    $data[$k] = $this->castValue($castType, $v);
                }
                return $data;
            case 'object-array':
            case 'array-collection':
                // create instances for the array values
                $data = array();
                $targetClass = $mdt['types'][$property][$type];
                foreach ($value as $k => $v) {
                    $data[$k] = $this->doDeserialize($v, $targetClass);
                }
                return $type == 'array-collection' ? new \Doctrine\Common\Collections\ArrayCollection($data) : $data;
            case 'object':
                // create single instance
                return $this->doDeserialize($value, $mdt['types'][$property]['object']);
            case 'DateTime':
                // if no value is provided
                if (strlen(trim($value)) == 0) {
                    return null;
                }
                // create instance according the specified format (or use the default format)
                $format = 'r';
                if (isset($mdt['types'][$property]['DateTime'])
                    && strlen($mdt['types'][$property]['DateTime']) > 0
                ) {
                    $format = $mdt['types'][$property]['DateTime'];
                }
                if (($dateTime = \DateTime::createFromFormat($format, $value)) === false) {
                    return null;
                }
                return $dateTime;
            default:
                return $this->castValue($type, $value);
        }

    }

    protected function castValue($type, $value){
        $value = is_array($value) ? null : $value;
        switch ($type) {
            case 'integer':
                return intval($value);
            case 'float':
                return floatval($value);
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            default:
                // strings and unknown types
                return $value;
        }
    }

    /**
     * @param array|callable $filter
     * @return DeserializerInterface
     */
    public function setFilter($filter){
        $this->filter = $filter;
    }


}