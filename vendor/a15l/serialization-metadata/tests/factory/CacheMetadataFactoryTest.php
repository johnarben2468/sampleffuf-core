<?php


namespace a15l\serialization\metadata\tests\factory;


use a15l\serialization\metadata\factory\CacheMetadataFactory;
use a15l\serialization\metadata\loader\file\JSONLoader;
use a15l\serialization\metadata\loader\LazyMetadataLoader;
use Doctrine\Common\Cache\ArrayCache;

class CacheMetadataFactoryTest extends \PHPUnit_Framework_TestCase{


    public function testCache(){
        $cacheProvider = new ArrayCache();
        $loader = new JSONLoader(__DIR__ . '/../fixtures');
        $defaultConfig = $loader->getClassMetadataConfig('metadata.defaults');
        $lazyLoader = new LazyMetadataLoader($loader);
        $factory = new CacheMetadataFactory($lazyLoader, $cacheProvider, $defaultConfig);
        $class = 'a15l\serialization\metadata\tests\fixtures\Dummy';
        $dummyData = $factory->getClassMetadata($class);
        $this->assertArrayHasKey('readonly', $dummyData);
        $rawData = json_decode(file_get_contents(__DIR__ .
            '../../fixtures' . DIRECTORY_SEPARATOR . 'a15l.serialization.metadata.tests.fixtures.Dummy.json'), true);
        $this->assertEquals(json_encode($rawData), json_encode($dummyData));
        $this->assertTrue($cacheProvider->contains('class-metadata::' . $class));
        $this->assertEquals(json_encode($rawData), json_encode($cacheProvider->fetch('class-metadata::' . $class)));
    }

}
