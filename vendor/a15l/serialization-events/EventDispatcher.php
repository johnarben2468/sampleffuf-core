<?php

namespace a15l\serialization\events;

class EventDispatcher implements EventDispatcherInterface{

    /**
     * @var array
     */
    private $events = array();

    /**
     * @param $eventType serialize/deserialize/both
     * @param $eventName The event to listen on
     * @param array $subscriber [Class, method]
     * @return EventDispatcherInterface
     */
    public function addSubscriber($eventType, $eventName, array $subscriber){
        if ($eventType === EventDispatcherInterface::EVENT_BOTH) {
            $this->events[EventDispatcherInterface::EVENT_SERIALIZE][$eventName] = $subscriber;
            $this->events[EventDispatcherInterface::EVENT_DESERIALIZE][$eventName] = $subscriber;
            return $this;
        }
        $this->events[$eventType][$eventName] = $subscriber;
        return $this;
    }

    /**
     * @param string $eventType serialize/deserialize/both
     * @param string $eventName The event to listen on
     * @param callable $listener The listener
     * @return EventDispatcherInterface
     */
    public function addListener($eventType, $eventName, $listener){
        if ($eventType === EventDispatcherInterface::EVENT_BOTH) {
            $this->events[EventDispatcherInterface::EVENT_SERIALIZE][$eventName] = $listener;
            $this->events[EventDispatcherInterface::EVENT_DESERIALIZE][$eventName] = $listener;
            return $this;
        }
        $this->events[$eventType][$eventName] = $listener;
        return $this;
    }

    /**
     * @param $eventType serialize/deserialize
     * @param $eventName
     * @param mixed $value
     * @return mixed
     */
    public function dispatch($eventType, $eventName, $value){
        if (isset($this->events[$eventType][$eventName])) {
            return call_user_func_array($this->events[$eventType][$eventName], array($value));
        }
    }

    /**
     * @param string $eventType
     * @param string $eventName
     * @return bool
     */
    public function hasSubscriber($eventType, $eventName){
        return isset($this->events[$eventType][$eventName]);
    }

}