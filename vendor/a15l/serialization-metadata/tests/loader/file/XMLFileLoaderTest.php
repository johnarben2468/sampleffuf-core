<?php


namespace a15l\serialization\metadata\tests\loader\file;


use a15l\serialization\metadata\loader\file\XMLFileLoader;

class XMLFileLoaderTest extends \PHPUnit_Framework_TestCase{

    public function testFileLoading(){
        $loader = new XMLFileLoader(__DIR__ . '/../../fixtures');
        $data = $loader->getClassMetadataConfig('metadata.defaults');
        $this->assertCount(1, $data);
        $this->assertArrayHasKey('ignore-all', $data);
        $dummyData = $loader->getClassMetadataConfig('a15l.serialization.metadata.tests.fixtures.Dummy');
        $this->assertArrayHasKey('readonly', $dummyData);
        $nonExistsing = $loader->getClassMetadataConfig('void');
        $this->assertNull($nonExistsing);
    }

}
