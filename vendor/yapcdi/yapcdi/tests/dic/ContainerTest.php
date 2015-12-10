<?php

namespace yapcdi\tests\dic;

use yapcdi\exception\InjectionException;

/**
 * Description of ContainerTest
 *
 * @author Alexander Schlegel
 */
class ContainerTest extends \PHPUnit_Framework_TestCase {

    private $container;
    private $resolver;

    public function setUp() {
        $this->resolver = new \yapcdi\resolver\ConfigResolver();
        $this->container = new \yapcdi\dic\Container($this->resolver);
    }

    public function testAliasWithResolver() {
        $this->resolver->setConfig(array(
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
        $b = $this->container->make('\yapcdi\tests\data\B');
        $this->assertInstanceOf('\yapcdi\tests\data\B', $b);
        $this->assertInstanceOf('\yapcdi\tests\data\X2', $b->getX());
    }

    public function testAliasWithContainerConfig() {
        try {
            $a = $this->container->make('\yapcdi\tests\data\A');
            $this->fail();
        } catch (\Exception $exc) {
            $this->assertInstanceOf('\yapcdi\exception\InjectionException', $exc);
            $this->assertEquals(InjectionException::CLASS_NOT_INSTANTIABLE, $exc->getCode());
        }
        $this->container->addAlias('\yapcdi\tests\data\BInterface', '\yapcdi\tests\data\B');
        try {
            $a = $this->container->make('yapcdi\tests\data\A');
            $this->fail();
        } catch (\Exception $exc) {
            $this->assertInstanceOf('\yapcdi\exception\InjectionException', $exc);
            $this->assertEquals(InjectionException::MISSING_REQUIRED_PARAM, $exc->getCode());
        }
        $this->container->addSharedParameter('foo', null)->reset();
        $a = $this->container->make('yapcdi\tests\data\A');
        $this->assertInstanceOf('\yapcdi\tests\data\A', $a);
        $this->assertInstanceOf('\yapcdi\tests\data\A', $a);
    }

    public function testAliasWithContainerPreConfig() {
        try {
            $a = $this->container->make('\yapcdi\tests\data\A');
            $this->fail();
        } catch (\Exception $exc) {
            $this->assertInstanceOf('\yapcdi\exception\InjectionException', $exc);
        }
        $this->container->setConfig(array(
            'aliases' => array(
                '\yapcdi\tests\data\BInterface' => '\yapcdi\tests\data\B'
            )
        ));
        try {
            $a = $this->container->make('yapcdi\tests\data\A');
            $this->fail();
        } catch (\Exception $exc) {
            $this->assertInstanceOf('\yapcdi\exception\InjectionException', $exc);
            $this->assertEquals(InjectionException::MISSING_REQUIRED_PARAM, $exc->getCode());
        }
        $this->container->addSharedParameter('foo', null)->reset();
        $a = $this->container->make('yapcdi\tests\data\A');
        $this->assertInstanceOf('\yapcdi\tests\data\A', $a);
    }

    public function testSharedInstancesWithResolver() {
        $sharedG = new \yapcdi\tests\data\G();
        $otherG = new \yapcdi\tests\data\G();
        $this->resolver->setConfig(array(
            'yapcdi\tests\data\E' => array(
                'constructor' => array(
                    'g' => array(
                        'value' => $sharedG
                    )
                )
            )
        ));
        $e = $this->container->make('yapcdi\tests\data\E');
        $this->assertInstanceOf('yapcdi\tests\data\E', $e);
        $this->assertSame($sharedG, $e->getG());
        $this->assertNotSame($otherG, $e->getG());
    }

    public function testSharedInstancesWithContainerConfig() {
        $sharedG = new \yapcdi\tests\data\G();
        $otherG = new \yapcdi\tests\data\G();
        $this->container->addSharedInstance($otherG);
        $e = $this->container->make('yapcdi\tests\data\E');
        $this->assertInstanceOf('yapcdi\tests\data\E', $e);
        $this->assertNotSame($sharedG, $e->getG());
        $this->assertSame($otherG, $e->getG());

        // if DIContainerInterface::reset is not called 
        // a internal cache will be used which returns the previously defined 
        // instance of G
        $this->container->addSharedInstance($sharedG, 'yapcdi\tests\data\G');
        $e1 = $this->container->make('yapcdi\tests\data\E');
        $this->assertInstanceOf('yapcdi\tests\data\E', $e1);
        $this->assertSame($otherG, $e1->getG());
        $this->assertNotSame($sharedG, $e1->getG());

        // if we reset the cache, the $sharedG instance of G will be used 
        $this->container->addSharedInstance($sharedG, 'yapcdi\tests\data\G')
                ->reset('yapcdi\tests\data\E');
        $e2 = $this->container->make('yapcdi\tests\data\E');
        $this->assertInstanceOf('yapcdi\tests\data\E', $e);
        $this->assertSame($sharedG, $e2->getG());
        $this->assertNotSame($otherG, $e2->getG());

        $callable = function() {
            static $g;
            if ($g === null) {
                $g = new \yapcdi\tests\data\G();
            }
            return $g;
        };
        $this->container->addSharedInstance($callable, 'yapcdi\tests\data\G')
                ->reset('yapcdi\tests\data\E');
        $e3 = $this->container->make('yapcdi\tests\data\E');
        $this->assertInstanceOf('yapcdi\tests\data\E', $e);
        $this->assertNotSame($sharedG, $e3->getG());
        $this->assertNotSame($otherG, $e3->getG());
        $this->assertSame($callable(), $e3->getG());

        $g = $this->container->make('yapcdi\tests\data\G');
        $this->assertInstanceOf('yapcdi\tests\data\G', $g);
        $this->assertNotSame($sharedG, $g);
        $this->assertNotSame($otherG, $g);
        $this->assertSame($callable(), $g);
    }

    public function testSharedInstancesWithContainerPreConfig() {
        $sharedG = new \yapcdi\tests\data\G();
        $otherG = new \yapcdi\tests\data\G();
        $this->container->setConfig(array(
            'sharedinstances' => array(
                '\yapcdi\tests\data\G' => $sharedG
            )
        ));
        $e = $this->container->make('yapcdi\tests\data\E');
        $this->assertInstanceOf('yapcdi\tests\data\E', $e);
        $this->assertNotSame($otherG, $e->getG());
        $this->assertSame($sharedG, $e->getG());
        $this->container->setConfig(array(
            'sharedinstances' => array(
                '\yapcdi\tests\data\G' => $otherG
            )
        ));
        $e1 = $this->container->make('yapcdi\tests\data\E');
        $this->assertInstanceOf('yapcdi\tests\data\E', $e1);
        $this->assertNotSame($sharedG, $e1->getG());
        $this->assertSame($otherG, $e1->getG());
        $callable = function() {
            static $g;
            if ($g === null) {
                $g = new \yapcdi\tests\data\G();
            }
            return $g;
        };
        $this->container->setConfig(array(
            'sharedinstances' => array(
                '\yapcdi\tests\data\G' => $callable
            )
        ));
        $e2 = $this->container->make('yapcdi\tests\data\E');
        $this->assertInstanceOf('yapcdi\tests\data\E', $e1);
        $this->assertNotSame($sharedG, $e2->getG());
        $this->assertNotSame($otherG, $e2->getG());
        $this->assertSame($callable(), $e2->getG());
    }

    public function testSharedParametersWithResolver() {
        try {
            $c = $this->container->make('yapcdi\tests\data\C');
            $this->fail();
        } catch (\Exception $exc) {
            $this->assertInstanceOf('yapcdi\exception\InjectionException', $exc);
            $this->assertEquals(InjectionException::MISSING_REQUIRED_PARAM, $exc->getCode());
        }
        $rand = uniqid();
        $this->resolver->setConfig(array(
            'yapcdi\tests\data\C' => array(
                'constructor' => array(
                    'foo' => array(
                        'value' => $rand
                    )
                )
            )
        ));
        $c = $this->container->reset('yapcdi\tests\data\C')
                ->make('yapcdi\tests\data\C');
        $this->assertInstanceOf('yapcdi\tests\data\C', $c);
        $this->assertEquals($rand, $c->getFoo());
    }

    public function testSharedParametersWithContainerConfig() {
        $rand = uniqid();
        $c = $this->container->addSharedParameter('foo', $rand)
                ->make('yapcdi\tests\data\C');
        $this->assertInstanceOf('yapcdi\tests\data\C', $c);
        $this->assertEquals($rand, $c->getFoo());
    }

    public function testSharedParametersWithContainerPreConfig() {
        $rand = uniqid();
        $c = $this->container->setConfig(array(
                    'sharedparameters' => array(
                        'foo' => $rand
                    )
                ))->make('yapcdi\tests\data\C');
        $this->assertInstanceOf('yapcdi\tests\data\C', $c);
        $this->assertEquals($rand, $c->getFoo());
    }

    public function testSharedClassParameters() {
        $x = new \yapcdi\tests\data\X();
        $x2 = new \yapcdi\tests\data\X2();
        $x3 = new \yapcdi\tests\data\X();
        $this->resolver->setConfig(array(
            'yapcdi\tests\data\B' => array(
                'setters' => array(
                    'setX' => array(
                        'x' => array(
                            'value' => $x
                        )
                    )
                )
            )
        ));
        $b = $this->container->make('yapcdi\tests\data\B');
        $this->assertInstanceOf('yapcdi\tests\data\B', $b);
        $this->assertInstanceOf('yapcdi\tests\data\X', $b->getX());
        $this->assertSame($x, $b->getX());

        $b2 = $this->container
                ->addSharedParameter('x', $x3)
                ->reset()
                ->make('yapcdi\tests\data\B');
        $this->assertInstanceOf('yapcdi\tests\data\B', $b2);
        $this->assertInstanceOf('yapcdi\tests\data\X', $b2->getX());
        $this->assertSame($x3, $b2->getX());

        $b3 = $this->container
                ->addSharedClassParameters('yapcdi\tests\data\B', 'setX', array(
                    'x' => $x2
                ))
                ->reset()
                ->make('yapcdi\tests\data\B');
        $this->assertInstanceOf('yapcdi\tests\data\B', $b3);
        $this->assertInstanceOf('yapcdi\tests\data\X2', $b3->getX());
        $this->assertSame($x2, $b3->getX());
    }

    public function testSharedClassParametersWithPreConfig() {
        $x = new \yapcdi\tests\data\X();
        $x2 = new \yapcdi\tests\data\X2();
        $x3 = new \yapcdi\tests\data\X();
        $this->resolver->setConfig(array(
            'yapcdi\tests\data\B' => array(
                'setters' => array(
                    'setX' => array(
                        'x' => array(
                            'value' => $x
                        )
                    )
                )
            )
        ));
        $b = $this->container->make('yapcdi\tests\data\B');
        $this->assertInstanceOf('yapcdi\tests\data\B', $b);
        $this->assertInstanceOf('yapcdi\tests\data\X', $b->getX());
        $this->assertSame($x, $b->getX());

        $config = array(
            'sharedparameters' => array(
                'x' => $x3
            )
        );

        $b2 = $this->container
                ->setConfig($config)
                ->make('yapcdi\tests\data\B');
        $this->assertInstanceOf('yapcdi\tests\data\B', $b2);
        $this->assertInstanceOf('yapcdi\tests\data\X', $b2->getX());
        $this->assertSame($x3, $b2->getX());

        $config['sharedclassparameters'] = array(
            'yapcdi\tests\data\B' => array(
                'setX' => array(
                    'x' => $x2
                )
            )
        );

        $b3 = $this->container
                ->setConfig($config)
                ->make('yapcdi\tests\data\B');
        $this->assertInstanceOf('yapcdi\tests\data\B', $b3);
        $this->assertInstanceOf('yapcdi\tests\data\X2', $b3->getX());
        $this->assertSame($x2, $b3->getX());
    }

    public function testPropertyInjectionsWithResolver() {
        $b = $this->container->make('yapcdi\tests\data\B');
        $this->assertInstanceOf('yapcdi\tests\data\B', $b);
        $this->assertNull($b->getX());
        $this->assertNull($b->getY());
        $this->assertNull($b->getZ());
        $z = new \yapcdi\tests\data\Z();
        $this->resolver->setConfig(array(
            'yapcdi\tests\data\B' => array(
                'properties' => array(
                    'x' => array(
                        'class' => 'yapcdi\tests\data\X2'
                    ),
                    'y' => array(
                        'class' => 'yapcdi\tests\data\Y'
                    ),
                    'z' => array(
                        'value' => $z
                    )
                )
            )
        ));
        $b = $this->container->reset('yapcdi\tests\data\B')
                ->make('yapcdi\tests\data\B');
        $this->assertInstanceOf('yapcdi\tests\data\B', $b);
        $this->assertInstanceOf('yapcdi\tests\data\X2', $b->getX());
        $this->assertInstanceOf('yapcdi\tests\data\Y', $b->getY());
        $this->assertInstanceOf('yapcdi\tests\data\Z', $b->getZ());
    }

    public function testClousureFactory() {
        $cf = new \yapcdi\factory\ClosureFactory();
        $x2 = new \yapcdi\tests\data\X2();
        $bFactory = function() use ($x2) {
            $b = new \yapcdi\tests\data\B();
            $b->setX($x2);
            $b->setY(new \yapcdi\tests\data\Y());
            $b->setZ(new \yapcdi\tests\data\Z);
            return $b;
        };
        $cf->add('yapcdi\tests\data\B', $bFactory);
        $this->container->addSharedInstance($cf, 'yapcdi\tests\data\B');
        $b = $this->container->make('yapcdi\tests\data\B');
        $this->assertInstanceOf('yapcdi\tests\data\B', $b);
        $this->assertSame($x2, $b->getX());

        $dFactory = function() {
            return new \yapcdi\tests\data\D();
        };
        $cf->addShared('d', $dFactory);
        $this->resolver->setConfig(array(
            'yapcdi\tests\data\A' => array(
                'setters' => array(
                    'setD' => array(
                        'd' => array(
                            'value' => $cf
                        )
                    )
                ),
                'constructor' => array(
                    'b' => array(
                        'class' => 'yapcdi\tests\data\B'
                    )
                )
            ),
            'yapcdi\tests\data\C' => array(
                'constructor' => array(
                    'foo' => array(
                        'value' => null
                    )
                )
            )
        ));
        $a = $this->container->reset()->make('yapcdi\tests\data\A');
        $this->assertInstanceOf('yapcdi\tests\data\A', $a);
        $this->assertSame($cf->create('d'), $a->getD());
    }

    /**
     * @expectedException \LogicException
     */
    public function testClousureFactoryException() {
        $cf = new \yapcdi\factory\ClosureFactory();
        $cf->create(uniqid());
    }

    /**
     * @expectedException yapcdi\exception\CircularDependencyException
     */
    public function testCircularDependencyException() {
        $this->container->make('yapcdi\tests\data\CircularDependency');
    }

}
