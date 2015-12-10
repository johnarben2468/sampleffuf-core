<?php

namespace yapcdi\tests\resolver;

/**
 * Description of ConfigResolverTest
 *
 * @author Alexander Schlegel
 */
class ConfigResolverTest extends \PHPUnit_Framework_TestCase {

    public function setUp() {
        require_once 'tests/data/TestClasses.php';
    }

    public function testPropertyInjections() {
        $resolver = new \yapcdi\resolver\ConfigResolver();
        $resolver->setConfig(array(
            'yapcdi\tests\data\B' => array(
                'properties' => array(
                    'x' => array(
                        'class' => '\yapcdi\tests\data\X'
                    ),
                    'y' => array(
                        'value' => new \yapcdi\tests\data\Y()
                    )
                )
            )
        ));
        $class = new \ReflectionClass('\yapcdi\tests\data\B');
        $info = $resolver->getClassDependencies($class);
        $this->assertArrayHasKey('constructor', $info);
        $this->assertArrayHasKey('properties', $info);
        $this->assertArrayHasKey('setters', $info);
        $this->assertCount(2, $info['properties']);
        $this->assertCount(0, $info['setters']);
        $this->assertCount(0, $info['constructor']);
        // property x
        $this->assertArrayHasKey('x', $info['properties']);
        $this->assertArrayNotHasKey('value', $info['properties']['x']);
        $this->assertArrayHasKey('class', $info['properties']['x']);
        $this->assertEquals('\yapcdi\tests\data\X', $info['properties']['x']['class']);
        // property y
        $this->assertArrayHasKey('y', $info['properties']);
        $this->assertArrayHasKey('value', $info['properties']['y']);
        $this->assertArrayNotHasKey('class', $info['properties']['y']);
        $this->assertInstanceOf('\yapcdi\tests\data\Y', $info['properties']['y']['value']);
    }

    public function testSetters() {
        $resolver = new \yapcdi\resolver\ConfigResolver();
        $resolver->setConfig(array(
            'yapcdi\tests\data\B' => array(
                'setters' => array(
                    'setX' => array(
                        'x' => array(
                            'class' => '\yapcdi\tests\data\X2'
                        )
                    )
                )
            )
        ));
        $class = new \ReflectionClass('\yapcdi\tests\data\B');
        $info = $resolver->getClassDependencies($class);
        $this->assertArrayHasKey('constructor', $info);
        $this->assertArrayHasKey('properties', $info);
        $this->assertArrayHasKey('setters', $info);
        $this->assertCount(0, $info['properties']);
        $this->assertCount(1, $info['setters']);
        $this->assertCount(0, $info['constructor']);
        // setX method check
        $this->assertArrayHasKey('setX', $info['setters']);
        // method parameter check
        $this->assertCount(1, $info['setters']['setX']);
        // parameter x check
        $this->assertArrayHasKey('x', $info['setters']['setX']);
        $this->assertArrayHasKey('class', $info['setters']['setX']['x']);
        $this->assertEquals('\yapcdi\tests\data\X2', $info['setters']['setX']['x']['class']);
    }

    public function testConstructors() {
        $resolver = new \yapcdi\resolver\ConfigResolver(false);
        $resolver->setConfig(array(
            // default parameter value for constructors
            'yapcdi\tests\data\C' => array(
                'constructor' => array(
                    'foo' => array(
                        'value' => 'fooBarFoo'
                    )
                )
            ),
            // class alias test
            'yapcdi\tests\data\A' => array(
                'constructor' => array(
                    'b' => array(
                        'class' => '\yapcdi\tests\data\B'
                    )
                )
            )
        ));
        $class = new \ReflectionClass('\yapcdi\tests\data\C');
        $info = $resolver->getClassDependencies($class);
        $this->assertArrayHasKey('constructor', $info);
        $this->assertArrayHasKey('properties', $info);
        $this->assertArrayHasKey('setters', $info);
        // parameters check
        $this->assertCount(4, $info['constructor']);
        $this->assertArrayHasKey('e', $info['constructor']);
        $this->assertArrayHasKey('f', $info['constructor']);
        $this->assertArrayHasKey('foo', $info['constructor']);
        $this->assertArrayHasKey('bar', $info['constructor']);
        // parameters type / value check
        // dependency e
        $this->assertArrayHasKey('class', $info['constructor']['e']);
        $this->assertEquals('yapcdi\tests\data\E', ltrim($info['constructor']['e']['class'], '\\'));
        // dependency f
        $this->assertArrayHasKey('class', $info['constructor']['f']);
        $this->assertEquals('yapcdi\tests\data\F', ltrim($info['constructor']['f']['class'], '\\'));
        // check default value defined in the configuration for foo (fooBarFoo)
        $this->assertArrayNotHasKey('class', $info['constructor']['foo']);
        $this->assertArrayHasKey('value', $info['constructor']['foo']);
        $this->assertEquals('fooBarFoo', $info['constructor']['foo']['value']);
        // check default value defined in the constructor for the parameter bar
        $this->assertArrayNotHasKey('class', $info['constructor']['bar']);
        $this->assertArrayHasKey('value', $info['constructor']['bar']);
        $this->assertEquals('bar', $info['constructor']['bar']['value']);
    }

}
