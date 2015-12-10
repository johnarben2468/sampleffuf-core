<?php


namespace a15l\serialization\metadata\tests\factory;


use a15l\serialization\metadata\factory\MetadataFactory;
use a15l\serialization\metadata\loader\file\JSONLoader;
use a15l\serialization\metadata\loader\file\PHPFileLoader;
use a15l\serialization\metadata\loader\file\XMLFileLoader;
use a15l\serialization\metadata\loader\LazyMetadataLoader;

class MetadataFactoryTest extends \PHPUnit_Framework_TestCase{

    public function testJSONLoader(){
        $loader = new JSONLoader(__DIR__ . '/../fixtures');
        $defaultConfig = $loader->getClassMetadataConfig('metadata.defaults');
        $lazyLoader = new LazyMetadataLoader($loader);
        $factory = new MetadataFactory($lazyLoader, $defaultConfig);
        $dummyData = $factory->getClassMetadata('a15l\serialization\metadata\tests\fixtures\Dummy');
        $this->assertArrayHasKey('readonly', $dummyData);
        $rawData = json_decode(file_get_contents(__DIR__ .
                                                 '../../fixtures' . DIRECTORY_SEPARATOR .
                                                 'a15l.serialization.metadata.tests.fixtures.Dummy.json'), true);
        $this->assertEquals(json_encode($rawData), json_encode($dummyData));
    }

    public function testXMLLoader(){
        $loader = new XMLFileLoader(__DIR__ . '/../fixtures');
        $defaultConfig = $loader->getClassMetadataConfig('metadata.defaults');
        $lazyLoader = new LazyMetadataLoader($loader);
        $factory = new MetadataFactory($lazyLoader, $defaultConfig);
        $dummyData = $factory->getClassMetadata('a15l\serialization\metadata\tests\fixtures\Dummy');
        $this->assertArrayHasKey('readonly', $dummyData);
        $rawData = json_decode(file_get_contents(__DIR__ .
                                                 '/../fixtures' . DIRECTORY_SEPARATOR .
                                                 'a15l.serialization.metadata.tests.fixtures.Dummy.json'), true);
        $this->assertEquals(json_encode($rawData), json_encode($dummyData));

        // non existing => check defaults
        $data2 = $factory->getClassMetadata('a15l\serialization\metadata\tests\fixtures\DummyIgnored');
        $this->assertEquals(json_encode($defaultConfig), json_encode($data2));

        // check remove of ignore-all for empty config files
        $data3 = $factory->getClassMetadata('a15l\serialization\metadata\tests\fixtures\DummyDefault');
        $this->assertArrayNotHasKey('ignore-all', $data3);
    }

    public function testPHPLoader(){
        $loader = new PHPFileLoader(__DIR__ . '/../fixtures');
        $defaultConfig = $loader->getClassMetadataConfig('metadata.defaults');
        $lazyLoader = new LazyMetadataLoader($loader);
        $factory = new MetadataFactory($lazyLoader, $defaultConfig);
        $dummyData = $factory->getClassMetadata('a15l\serialization\metadata\tests\fixtures\Dummy');
        $this->assertArrayHasKey('readonly', $dummyData);
        $rawData = json_decode(file_get_contents(__DIR__ .
                                                 '../../fixtures' . DIRECTORY_SEPARATOR .
                                                 'a15l.serialization.metadata.tests.fixtures.Dummy.json'), true);
        $this->assertEquals(json_encode($rawData), json_encode($dummyData));
    }
}
