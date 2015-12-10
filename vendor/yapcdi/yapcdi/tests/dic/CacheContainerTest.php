<?php

namespace yapcdi\tests\dic;

/**
 * Description of ContainerTest
 *
 * @author Alexander Schlegel
 */
class CacheContainerTest extends \PHPUnit_Framework_TestCase {

    private $container;
    private $resolver;
    private $provider;

    public function setUp() {
        $this->resolver = new \yapcdi\resolver\ConfigResolver();
        $this->provider = new \Doctrine\Common\Cache\ArrayCache();
        $this->container = new \yapcdi\dic\CacheContainer($this->resolver, $this->provider);
    }

    public function testEmptyCache() {
        $this->assertFalse($this->provider->contains('yapcdi\tests\data\B#info'));
        $b = $this->container->make('yapcdi\tests\data\B');
        $this->assertTrue($this->provider->contains('yapcdi\tests\data\B#info'));
        $this->assertInstanceOf('yapcdi\tests\data\B', $b);
        $this->assertNull($b->getX());
    }

    public function testCacheHit() {
        $this->provider->save('yapcdi\tests\data\B#info', array(
            'constructor' => array(),
            'properties' => array(),
            'setters' => array(
                'setX' => array(
                    'x' => array(
                        'class' => 'yapcdi\tests\data\X2'
                    )
                )
            )
        ));
        $b = $this->container->make('yapcdi\tests\data\B');
        $this->assertInstanceOf('yapcdi\tests\data\B', $b);
        $this->assertInstanceOf('yapcdi\tests\data\X2', $b->getX());
    }

    public function testCacheReset() {
        $id = 'yapcdi\tests\data\B#info';
        $this->provider->save($id, array(
            'constructor' => array(),
            'properties' => array(),
            'setters' => array(
                'setX' => array(
                    'x' => array(
                        'class' => 'yapcdi\tests\data\X2'
                    )
                )
            )
        ));
        $this->provider->save('yapcdi\tests\data\X#info', array(
            'constructor' => array(),
            'properties' => array(),
            'setters' => array()
        ));
        $b = $this->container->make('yapcdi\tests\data\B');
        $this->assertInstanceOf('yapcdi\tests\data\B', $b);
        $this->assertInstanceOf('yapcdi\tests\data\X2', $b->getX());
        $result = $this->provider->fetch($id);
        $this->assertNotFalse($result);
        $this->assertArrayHasKey('setters', $result);
        $this->assertArrayHasKey('setX', $result['setters']);
        $this->container->reset('yapcdi\tests\data\B');
        $result2 = $this->provider->fetch($id);
        $this->assertFalse($result2);
        $result3 = $this->provider->fetch('yapcdi\tests\data\X#info');
        $this->assertNotFalse($result3);
        $this->assertArrayHasKey('setters', $result3);
        $this->container->reset();
        $result4 = $this->provider->fetch($id);
        $result5 = $this->provider->fetch('yapcdi\tests\data\X#info');
        $this->assertFalse($result4);
        $this->assertFalse($result5);
    }

}
