<?php


namespace a15l\serialization\metadata\tests\loader;


use a15l\serialization\metadata\loader\file\JSONLoader;
use a15l\serialization\metadata\loader\file\PHPFileLoader;
use a15l\serialization\metadata\loader\file\XMLFileLoader;
use a15l\serialization\metadata\loader\LazyMetadataLoader;

class LazyMetadataLoaderTest extends \PHPUnit_Framework_TestCase{

    public function testJSONLoader(){
        $loader = new JSONLoader(__DIR__ . '/../fixtures');
        $lazyLoader = new LazyMetadataLoader($loader);
        $dummyData = $lazyLoader->getMetadata('a15l\serialization\metadata\tests\fixtures\Dummy');
        $this->assertArrayHasKey('readonly', $dummyData);
        $rawData = json_decode(file_get_contents(__DIR__ .
            '../../fixtures' . DIRECTORY_SEPARATOR . 'a15l.serialization.metadata.tests.fixtures.Dummy.json'), true);
        $this->assertEquals(json_encode($rawData), json_encode($dummyData));
    }

    public function testPHPLoader(){
        $loader = new PHPFileLoader(__DIR__ . '/../fixtures');
        $lazyLoader = new LazyMetadataLoader($loader);
        $dummyData = $lazyLoader->getMetadata('a15l\serialization\metadata\tests\fixtures\Dummy');
        $this->assertArrayHasKey('readonly', $dummyData);
        $rawData = json_decode(file_get_contents(__DIR__ .
            '../../fixtures' . DIRECTORY_SEPARATOR . 'a15l.serialization.metadata.tests.fixtures.Dummy.json'), true);
        $this->assertEquals(json_encode($rawData), json_encode($dummyData));
    }

    public function testXMLLoader(){
        $loader = new XMLFileLoader(__DIR__ . '/../fixtures');
        $lazyLoader = new LazyMetadataLoader($loader);
        $dummyData = $lazyLoader->getMetadata('a15l\serialization\metadata\tests\fixtures\Dummy');
        $this->assertArrayHasKey('readonly', $dummyData);
        $rawData = json_decode(file_get_contents(__DIR__ .
            '../../fixtures' . DIRECTORY_SEPARATOR . 'a15l.serialization.metadata.tests.fixtures.Dummy.json'), true);
        $this->assertEquals(json_encode($rawData), json_encode($dummyData));
    }
}
