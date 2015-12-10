<?php

namespace yapcdi\controller\resolver\symfony\tests;

interface FooServiceInterFace {

    public function foo();

    public function bar($foo);
}

class FooService implements FooServiceInterFace {

    public function foo() {
        return new \Symfony\Component\HttpFoundation\Response('foo');
    }

    public function bar($foo) {
        return new \Symfony\Component\HttpFoundation\Response($foo);
    }

}

class FooController {

    private $fooService;

    public function __construct(FooServiceInterFace $fooService) {
        $this->fooService = $fooService;
    }

    public function doFoo() {
        return $this->fooService->foo();
    }

    public function doBar($foo) {
        return $this->fooService->bar($foo);
    }

    public function doDefaultBar($foo = 'default') {
        return $this->fooService->bar($foo);
    }

}

class ControllerOverrider implements \Symfony\Component\EventDispatcher\EventSubscriberInterface {

    public function onFilterController(\Symfony\Component\HttpKernel\Event\FilterControllerEvent $e) {
        $e->setController(function() {
            return new \Symfony\Component\HttpFoundation\Response('void');
        });
    }

    public static function getSubscribedEvents() {
        return array(
            \Symfony\Component\HttpKernel\KernelEvents::CONTROLLER => array(
                array('onFilterController', 0)
            )
        );
    }

}
