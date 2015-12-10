<?php


namespace a15l\serialization\deserializer\tests;


use a15l\serialization\deserializer\HTTPQueryStringDeserializer;
use a15l\serialization\events\EventDispatcher;
use a15l\serialization\metadata\factory\MetadataFactory;
use a15l\serialization\metadata\loader\file\XMLFileLoader;
use a15l\serialization\metadata\loader\LazyMetadataLoader;

class HTTPQueryStringDeserializerTest extends \PHPUnit_Framework_TestCase{

    /**
     * @var LazyMetadataLoader
     */
    private $metadataLoader;

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    protected function setUp(){
        $fileLoader = new XMLFileLoader(__DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR);
        $this->metadataLoader = new LazyMetadataLoader($fileLoader);
        $this->dispatcher = new EventDispatcher();
    }


    public function testListDeserialization(){
        $factory = new MetadataFactory($this->metadataLoader);
        $deserializer = new HTTPQueryStringDeserializer($this->dispatcher, $factory);
        $data = '0%5BfooValue%5D=some+value.appended&0%5BfooDate%5D=2015-01-20'
            . '&0%5Bbar%5D%5BaliasName%5D=a1&0%5Bbar%5D%5BaliasName2%5D=a2&0%5Bbar%5D%5BscalarArray%5D%5B0%5D=1&0'
            . '%5Bbar%5D%5BscalarArray%5D%5B1%5D=2&0%5Bbar%5D%5BscalarArray%5D%5B2%5D=3&0%5Bbars%5D%5B0%5D%5BaliasName'
            . '%5D=a1&0%5Bbars%5D%5B0%5D%5BaliasName2%5D=a2&0%5Bbars%5D%5B0%5D%5BscalarArray%5D%5B0%5D=1&0%5B'
            . 'bars%5D%5B0%5D%5BscalarArray%5D%5B1%5D=2&0%5Bbars%5D%5B0%5D%5BscalarArray%5D%5B2%5D=3&1%5BfooValue%'
            . '5D=some+value.appended&1%5BfooDate%5D=2015-01-20'
            . '&1%5Bbar%5D%5BaliasName%5D=a1&1%5Bbar%5D%5BaliasName2'
            . '%5D=a2&1%5Bbar%5D%5BscalarArray%5D%5B0%5D=1&1%5Bbar%5D%5BscalarArray%5D%5B1%5D=2&1%5Bbar%5D%5'
            . 'BscalarArray%5D%5B2%5D=3&1%5Bbars%5D%5B0%5D%5BaliasName%5D=a1&1%5Bbars%5D%5B0%5D%5BaliasName2%5D=a'
            . '2&1%5Bbars%5D%5B0%5D%5BscalarArray%5D%5B0%5D=1&1%5Bbars%5D%5B0%5D%5BscalarArray%5D%5B1%5D=2&1%5Bbars'
            . '%5D%5B0%5D%5BscalarArray%5D%5B2%5D=3';
        /**@var $foos Foo[] */
        $foos = $deserializer->deserialize($data, 'a15l\serialization\deserializer\tests\fixtures\Foo');
        $this->assertCount(2, $foos);
        foreach ($foos as $foo) {
            $this->assertInstanceOf('a15l\serialization\deserializer\tests\fixtures\Foo', $foo);
            $this->assertNull($foo->getIgnored());
            $this->assertEquals('some value.appended', $foo->getFooValue());
            $this->assertInstanceOf('\DateTime', $foo->getFooDate());
            $this->assertEquals('2015-01-20', $foo->getFooDate()->format('Y-m-d'));
            $this->assertCount(1, $foo->getBars());
            $this->assertInstanceOf('a15l\serialization\deserializer\tests\fixtures\Bar', $foo->getBar());
            $this->assertEquals('a1', $foo->getBar()->getAlias1());
        }
    }

    public function testDeserialization(){
        $factory = new MetadataFactory($this->metadataLoader);
        $deserializer = new HTTPQueryStringDeserializer($this->dispatcher, $factory);
        $data = 'fooValue=value.appended&fooDate=2015-01-20'
            . '&bar%5BaliasName%5D=a1&bar%5BaliasName2%5D=a2&bar%5BscalarArray%5D%5B0%5D=1&bar%5BscalarArray'
            . '%5D%5B1%5D=2&bar%5BscalarArray%5D%5B2%5D=3&bar%5BscalarArray%5D%5B3%5D=4&bars%5B0%5D%5BaliasName%5D='
            . 'a1&bars%5B0%5D%5BaliasName2%5D=a2&bars%5B0%5D%5BscalarArray%5D%5B0%5D=1&bars%5B0%5D%5BscalarArray%5D'
            . '%5B1%5D=2&bars%5B0%5D%5BscalarArray%5D%5B2%5D=3&bars%5B0%5D%5BscalarArray%5D%5B3%5D=4&bars%5B1%5D%5'
            . 'BaliasName%5D=a1&bars%5B1%5D%5BaliasName2%5D=a2&bars%5B1%5D%5BscalarArray%5D%5B0%5D=1&bars%5B1%5D%5'
            . 'BscalarArray%5D%5B1%5D=2&bars%5B1%5D%5BscalarArray%5D%5B2%5D=3&bars%5B1%5D%5BscalarArray%5D%5B3%5D=4'
            . '&bars%5B2%5D%5BaliasName%5D=a1&bars%5B2%5D%5BaliasName2%5D=a2&bars%5B2%5D%5BscalarArray%5D%5B0%5D=1&'
            . 'bars%5B2%5D%5BscalarArray%5D%5B1%5D=2&bars%5B2%5D%5BscalarArray%5D%5B2%5D=3&bars%5B2%5D%5BscalarArray'
            . '%5D%5B3%5D=4';
        /**@var $foo Foo */
        $foo = $deserializer->deserialize($data, 'a15l\serialization\deserializer\tests\fixtures\Foo');
        $this->assertInstanceOf('a15l\serialization\deserializer\tests\fixtures\Foo', $foo);
        $this->assertNull($foo->getIgnored());
        $this->assertEquals('value.appended', $foo->getFooValue());
        $this->assertInstanceOf('\DateTime', $foo->getFooDate());
        $this->assertEquals('2015-01-20', $foo->getFooDate()->format('Y-m-d'));
        $this->assertCount(3, $foo->getBars());
        $this->assertInstanceOf('a15l\serialization\deserializer\tests\fixtures\Bar', $foo->getBar());
        $this->assertEquals('a1', $foo->getBar()->getAlias1());
    }
}
