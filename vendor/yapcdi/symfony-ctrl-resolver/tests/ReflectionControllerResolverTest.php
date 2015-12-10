<?php

namespace yapcdi\controller\resolver\symfony\tests;

/**
 * Description of ReflectionControllerResolverTest
 *
 * @author Alexander Schlegel
 */
class ReflectionControllerResolverTest extends \PHPUnit_Framework_TestCase {

    private $kernel;
    private $resolver;
    private $dispatcher;

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
        $logger = new \Symfony\Component\HttpKernel\Log\NullLogger();
        $diContainer = new \yapcdi\dic\Container($this->resolver);
        $this->dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
        $controllerResolver = new \yapcdi\controller\resolver\symfony\ReflectionControllerResolver($diContainer, $logger);
        $this->kernel = new \Symfony\Component\HttpKernel\HttpKernel($this->dispatcher, $controllerResolver);
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testEmptyController() {
        $r = new \Symfony\Component\HttpFoundation\Request();
        $this->kernel->handle($r);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unable to find controller "foo".
     */
    public function testMalformedControllerName() {
        $r = new \Symfony\Component\HttpFoundation\Request();
        $r->attributes->set('_controller', 'foo');
        $this->kernel->handle($r);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Class "foo" does not exist
     */
    public function testMissingControllerClass() {
        $r = new \Symfony\Component\HttpFoundation\Request();
        $r->attributes->set('_controller', 'foo::bar');
        $this->kernel->handle($r);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "yapcdi\controller\resolver\symfony\tests\FooController::missingMethod" for URI "/" is not callable.
     */
    public function testMissingControllerMethod() {
        $r = new \Symfony\Component\HttpFoundation\Request();
        $r->attributes->set('_controller', 'yapcdi\controller\resolver\symfony\tests\FooController::missingMethod');
        $this->kernel->handle($r);
    }

    public function testControllerMethodWithoutParameters() {
        $r = new \Symfony\Component\HttpFoundation\Request();
        $r->attributes->set('_controller', 'yapcdi\controller\resolver\symfony\tests\FooController::doFoo');
        $response = $this->kernel->handle($r);
        $this->assertEquals('foo', $response->getContent());
    }

    public function testControllerMethodWithDefaultParameters() {
        $r = new \Symfony\Component\HttpFoundation\Request();
        $r->attributes->set('_controller', 'yapcdi\controller\resolver\symfony\tests\FooController::doDefaultBar');
        $response = $this->kernel->handle($r);
        $this->assertEquals('default', $response->getContent());

        $r2 = new \Symfony\Component\HttpFoundation\Request();
        $r2->attributes->set('_controller', 'yapcdi\controller\resolver\symfony\tests\FooController::doDefaultBar');
        $r2->attributes->set('foo', 'default2');
        $response2 = $this->kernel->handle($r2);
        $this->assertEquals('default2', $response2->getContent());
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
