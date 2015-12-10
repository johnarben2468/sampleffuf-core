<?php

namespace yapcdi\controller\resolver\symfony\tests;

/**
 * Description of ReflectionControllerResolverTest
 *
 * @author Alexander Schlegel
 */
class ReflectionCacheControllerResolverTest extends \PHPUnit_Framework_TestCase {

    private $kernel;
    private $resolver;
    private $dispatcher;
    private $provider;

    public function setUp() {
        require_once 'tests/data/TestClasses.php';
        $this->resolver = new \yapcdi\resolver\ConfigResolver();
        $this->resolver->setConfig(array(
            'yapcdi\controller\resolver\symfony\tests\FooController' => array(
                'constructor' => array(
                    'fooService' => array(
                        'class' => 'yapcdi\controller\resolver\symfony\tests\FooService'
                    )
                )
            )
        ));
        $this->provider = new \Doctrine\Common\Cache\ArrayCache();
        $logger = new \Symfony\Component\HttpKernel\Log\NullLogger();
        $diContainer = new \yapcdi\dic\Container($this->resolver);
        $this->dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
        $controllerResolver = new \yapcdi\controller\resolver\symfony\ReflectionCacheControllerResolver($diContainer, $this->provider, $logger);
        $this->kernel = new \Symfony\Component\HttpKernel\HttpKernel($this->dispatcher, $controllerResolver);
    }

    public function testControllerMethodWithoutParameters() {
        $id = 'yapcdi\controller\resolver\symfony\tests\FooController::doFoo#method-parameters';
        $this->assertFalse($this->provider->fetch($id));
        $r = new \Symfony\Component\HttpFoundation\Request();
        $r->attributes->set('_controller', 'yapcdi\controller\resolver\symfony\tests\FooController::doFoo');
        $response = $this->kernel->handle($r);
        $this->assertEquals('foo', $response->getContent());
        $cache = $this->provider->fetch($id);
        $this->assertNotFalse($cache);
        $this->assertCount(0, $cache);
    }

    public function testControllerMethodWithDefaultParameters() {
        $r = new \Symfony\Component\HttpFoundation\Request();
        $r->attributes->set('_controller', 'yapcdi\controller\resolver\symfony\tests\FooController::doDefaultBar');
        $response = $this->kernel->handle($r);
        $this->assertEquals('default', $response->getContent());

        $id = 'yapcdi\controller\resolver\symfony\tests\FooController::doDefaultBar#method-parameters';
        $value = uniqid();
        $this->provider->save($id, array('foo' => array('defaultValue' => $value)));
        $response2 = $this->kernel->handle($r);
        $this->assertEquals($value, $response2->getContent());
        $value2 = uniqid();
        $r->attributes->set('foo', $value2);
        $response3 = $this->kernel->handle($r);
        $this->assertEquals($value2, $response3->getContent());
    }

    public function testControllerMethodWithParameters() {
        $r = new \Symfony\Component\HttpFoundation\Request();
        $r->attributes->set('_controller', 'yapcdi\controller\resolver\symfony\tests\FooController::doBar');
        try {
            $this->kernel->handle($r);
            $this->fail();
        } catch (\Exception $exc) {
            $this->assertInstanceOf('RuntimeException', $exc);
            $this->assertEquals('Controller '
                    . 'yapcdi\controller\resolver\symfony\tests\FooController::doBar '
                    . 'requires that you provide a value for the "$foo" '
                    . 'argument (because there is no default value or because '
                    . 'there is a non optional argument after this one).', $exc->getMessage());
        }
        $value = uniqid();
        $r->attributes->set('foo', $value);
        $response = $this->kernel->handle($r);
        $this->assertEquals($value, $response->getContent());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Can not resolve arguments for the controller type: "closure" for URI "/"
     */
    public function testInvalidControllerType() {
        $t = new ControllerOverrider();
        $this->dispatcher->addSubscriber($t);
        $r = new \Symfony\Component\HttpFoundation\Request();
        $r->attributes->set('_controller', 'yapcdi\controller\resolver\symfony\tests\FooController::doFoo');
        $this->kernel->handle($r);
    }

}
