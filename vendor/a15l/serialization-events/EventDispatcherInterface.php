<?php

namespace a15l\serialization\events;

interface EventDispatcherInterface{

    const EVENT_SERIALIZE = 'serialize';
    const EVENT_DESERIALIZE = 'deserialize';
    const EVENT_BOTH = 'both';

    /**
     * @param $eventType serialize/deserialize/both
     * @param $eventName The event to listen on
     * @param array $subscriber [Class, method]
     * @return EventDispatcherInterface
     */
    public function addSubscriber($eventType, $eventName, array $subscriber);

    /**
     * @param string $eventType serialize/deserialize/both
     * @param string $eventName The event to listen on
     * @param callable $listener The listener
     * @return EventDispatcherInterface
     */
    public function addListener($eventType, $eventName, $listener);

    /**
     * @param $eventType serialize/deserialize/both
     * @param $eventName
     * @param mixed $value
     * @return mixed
     */
    public function dispatch($eventType, $eventName, $value);

    /**
     * @param string $eventType
     * @param string $eventName
     * @return boolean
     */
    public function hasSubscriber($eventType, $eventName);
}