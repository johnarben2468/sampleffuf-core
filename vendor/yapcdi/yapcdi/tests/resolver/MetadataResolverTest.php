<?php

namespace yapcdi\tests\resolver;

/**
 * Description of MetadataResolverTest
 *
 * @author Alexander Schlegel
 */
class MetadataResolverTest extends \PHPUnit_Framework_TestCase {

    private $resolver;

    public function setUp() {
        require_once 'tests/data/TestClasses.php';
        $this->resolver = new \yapcdi\resolver\MetadataResolver();
    }

    public function testPropertyMetadata() {
        $class = new \ReflectionClass('\yapcdi\tests\data\B');
        $classInfo = $this->resolver->getClassDependencies($class);
        // check required keys
        $this->assertArrayHasKey('constructor', $classInfo);
        $this->assertArrayHasKey('properties', $classInfo);
        $this->assertArrayHasKey('setters', $classInfo);
        // chek definitions count
        $this->assertCount(0, $classInfo['constructor']);
        $this->assertCount(3, $classInfo['properties']);
        $this->assertCount(0, $classInfo['setters']);
        //check property injection definitions
        $this->assertArrayHasKey('x', $classInfo['properties']);
        $this->assertArrayHasKey('y', $classInfo['properties']);
        $this->assertArrayHasKey('z', $classInfo['properties']);
        // dependency x
        $this->assertArrayHasKey('class', $classInfo['properties']['x']);
        $this->assertEquals('\yapcdi\tests\data\X', $classInfo['properties']['x']['class']);
        // dependency y
        $this->assertArrayHasKey('class', $classInfo['properties']['y']);
        $this->assertEquals('\yapcdi\tests\data\YInterface', $classInfo['properties']['y']['class']);
        // dependency z
        $this->assertArrayHasKey('class', $classInfo['properties']['z']);
        $this->assertEquals('\yapcdi\tests\data\Z', $classInfo['properties']['z']['class']);
        // exception test for comma detection in property annoations 
        // => @Inject("foo", "bar")
        try {

            $class2 = new \ReflectionClass('\yapcdi\tests\data\PropertyMetadataError');
            $this->resolver->getClassDependencies($class2);
            $this->fail();
        } catch (\Exception $exc) {
            $this->assertInstanceOf('\yapcdi\exception\AnnotationException', $exc);
        }
        // exception test for missing @var annotation detection for property 
        // annotations without class concretisation
        try {
            $class3 = new \ReflectionClass('\yapcdi\tests\data\PropertyMetadataError2');
            $this->resolver->getClassDependencies($class3);
            $this->fail();
        } catch (\Exception $exc) {
            $this->assertInstanceOf('\yapcdi\exception\AnnotationException', $exc);
        }
    }

    public function testSetters() {
        $class = new \ReflectionClass('\yapcdi\tests\data\A');
        $classInfo = $this->resolver->getClassDependencies($class);
        // check required keys
        $this->assertArrayHasKey('constructor', $classInfo);
        $this->assertArrayHasKey('properties', $classInfo);
        $this->assertArrayHasKey('setters', $classInfo);
        // chek definitions count
        $this->assertCount(2, $classInfo['constructor']);
        $this->assertCount(0, $classInfo['properties']);
        $this->assertCount(2, $classInfo['setters']);
        //check constructor injection definitions
        $this->assertArrayHasKey('b', $classInfo['constructor']);
        $this->assertArrayHasKey('c', $classInfo['constructor']);
        // dependency b (type BInterface => annotated with B as concrete value)
        $this->assertArrayHasKey('class', $classInfo['constructor']['b']);
        $this->assertEquals('\yapcdi\tests\data\B', $classInfo['constructor']['b']['class']);
        // dependency c (type C)
        $this->assertArrayHasKey('class', $classInfo['constructor']['c']);
        // ReflectionParameter::getClass returned class does'n contain 
        // the leading \ in the namespace, so remove it
        $this->assertEquals('yapcdi\tests\data\C', ltrim($classInfo['constructor']['c']['class'], '\\'));
        // null as class value test for missing method parameter values
        $class2 = new \ReflectionClass('\yapcdi\tests\data\SetterMetadataError');
        $class2Info = $this->resolver->getClassDependencies($class2);
        $this->assertArrayHasKey('setters', $class2Info);
        $this->assertArrayHasKey('foo', $class2Info['setters']);
        $this->assertArrayHasKey('bar', $class2Info['setters']['foo']);
        $this->assertArrayHasKey('class', $class2Info['setters']['foo']['bar']);
        $this->assertArrayNotHasKey('value', $class2Info['setters']['foo']['bar']);
        $this->assertNull($class2Info['setters']['foo']['bar']['class']);

        // exception test for missing parameter name in @Injection annotations
        // for setters
        try {
            $class3 = new \ReflectionClass('\yapcdi\tests\data\SetterMetadataError2');
            $this->resolver->getClassDependencies($class3);
            $this->fail();
        } catch (\Exception $exc) {
            $this->assertInstanceOf('\yapcdi\exception\AnnotationException', $exc);
        }
    }

    public function testConstructors() {
        $class = new \ReflectionClass('\yapcdi\tests\data\C');
        $info = $this->resolver->getClassDependencies($class);
        // check required keys
        $this->assertArrayHasKey('constructor', $info);
        $this->assertArrayHasKey('properties', $info);
        $this->assertArrayHasKey('setters', $info);
        $this->assertCount(4, $info['constructor']);
        // keys check
        $this->assertArrayHasKey('e', $info['constructor']);
        $this->assertArrayHasKey('f', $info['constructor']);
        $this->assertArrayHasKey('foo', $info['constructor']);
        $this->assertArrayHasKey('bar', $info['constructor']);
        // dependency e
        $this->assertArrayHasKey('class', $info['constructor']['e']);
        $this->assertEquals('yapcdi\tests\data\E', ltrim($info['constructor']['e']['class'], '\\'));
        // dependency f
        $this->assertArrayHasKey('class', $info['constructor']['f']);
        $this->assertEquals('yapcdi\tests\data\F', ltrim($info['constructor']['f']['class'], '\\'));
        // check default value annotaed for foo (@Inject("foo", "foo"))
        $this->assertArrayNotHasKey('class', $info['constructor']['foo']);
        $this->assertArrayHasKey('value', $info['constructor']['foo']);
        $this->assertEquals('fOo', $info['constructor']['foo']['value']);
        // check default value defined in the constructor for the parameter bar
        $this->assertArrayNotHasKey('class', $info['constructor']['bar']);
        $this->assertArrayHasKey('value', $info['constructor']['bar']);
        $this->assertEquals('bar', $info['constructor']['bar']['value']);

        // aliases of null are an empty array
        $aliases = $this->resolver->getMethodParameterAliases(null);
        $this->assertCount(0, $aliases);
    }

}
