<?php


namespace a15l\serialization\deserializer\tests;

use a15l\serialization\deserializer\JSONDeserializer;
use a15l\serialization\deserializer\JSONSerializer;
use a15l\serialization\deserializer\tests\fixtures\Foo;
use a15l\serialization\deserializer\tests\fixtures\FooBar;
use a15l\serialization\events\EventDispatcher;
use a15l\serialization\events\EventDispatcherInterface;
use a15l\serialization\metadata\factory\MetadataFactory;
use a15l\serialization\metadata\loader\file\XMLFileLoader;
use a15l\serialization\metadata\loader\LazyMetadataLoader;

class JSONDeserializerTest extends \PHPUnit_Framework_TestCase{

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
        $this->dispatcher->addListener(EventDispatcherInterface::EVENT_DESERIALIZE, 'fooValueChange', function ($value){
            return $value . '.appended';
        });
        $deserializer = new JSONDeserializer($this->dispatcher, $factory);
        $data = '[{"scalarArray": [1,222,333,444],"ignored": "invalid","fooValue":"some value.appended",'
                . '"fooDate":"2015-01-20","bar":{"aliasName":"a1","aliasName2":"a2"},"bars":'
                . '[{"aliasName":"a1","aliasName2":"a2"},' . '{"aliasName":"a1","aliasName2":"a2"}]},{"scalarArray": '
                . '[1,222,333,444],"ignored":"invalid","fooValue":"some value.appended",'
                . '"fooDate":"2015-01-20","bar":{"aliasName":"a1","aliasName2":"a2"},"'
                . 'bars":[{"aliasName":"a1","aliasName2":"a2"},{"aliasName":"a1","aliasName2":"a2"}]}]';
        /**@var $foos Foo[] */
        $foos = $deserializer->deserialize($data, 'a15l\serialization\deserializer\tests\fixtures\Foo');
        $this->assertCount(2, $foos);
        foreach ($foos as $foo) {
            $this->assertInstanceOf('a15l\serialization\deserializer\tests\fixtures\Foo', $foo);
            $this->assertNull($foo->getIgnored());
            $this->assertEquals('some value.appended.appended', $foo->getFooValue());
            $this->assertInstanceOf('\DateTime', $foo->getFooDate());
            $this->assertEquals('2015-01-20', $foo->getFooDate()->format('Y-m-d'));
            $this->assertCount(2, $foo->getBars());
            $this->assertInstanceOf('a15l\serialization\deserializer\tests\fixtures\Bar', $foo->getBar());
            $this->assertEquals('a1', $foo->getBar()->getAlias1());
        }
    }

    public function testDeserialization(){
        $factory = new MetadataFactory($this->metadataLoader);
        $deserializer = new JSONDeserializer($this->dispatcher, $factory);
        $data = '{"fooValue":"some value.appended","fooDate":"2015-01-31","bar":{"aliasName":'
                . '"alias 1 value","aliasName2":"alias 2 value","scalarArray":[1,2,3,4]},'
                . '"bars":[{"aliasName":"alias 1 value","aliasName2":"alias 2 value","scalarArray":[1,2,3,4]},'
                . '{"aliasName":"alias 1 value","aliasName2":"alias 2 value","scalarArray":[1,2,3,4]},'
                . '{"aliasName":"alias 1 value","aliasName2":"alias 2 value","scalarArray":[1,2,3,4]}]}';
        /**@var $foo Foo */
        $foo = $deserializer->deserialize($data, 'a15l\serialization\deserializer\tests\fixtures\Foo');
        $this->assertInstanceOf('a15l\serialization\deserializer\tests\fixtures\Foo', $foo);
    }

    public function testArrayIntTypeCast(){
        $factory = new MetadataFactory($this->metadataLoader);
        $deserializer = new JSONDeserializer($this->dispatcher, $factory);
        $data = '{"scalarArray": [0,1,22,33], "fooValue":"some value.appended","fooDate":"2015-01-31","bar":{"'
                . 'aliasName":"alias 1 value","aliasName2":"alias 2 value","scalarArray":[1,2,3,4]},'
                . '"bars":[{"aliasName":"alias 1 value","aliasName2":"alias 2 value","scalarArray":[1,2,3,4]},'
                . '{"aliasName":"alias 1 value","aliasName2":"alias 2 value","scalarArray":[1,2,3,4]},'
                . '{"aliasName":"alias 1 value","aliasName2":"alias 2 value","scalarArray":[1,2,3,4]}]}';
        /**@var $foo Foo */
        $foo = $deserializer->deserialize($data, 'a15l\serialization\deserializer\tests\fixtures\Foo');
        $array = $foo->getScalarArray();
        $this->assertCount(4, $array);
        $this->assertEquals(56, array_sum($array));

    }

    public function testArrayFloatTypeCast(){
        $factory = new MetadataFactory($this->metadataLoader, array(
            'types' => array(
                'mixedType' => array(
                    'scalar-array' => 'float'
                )
            )
        ));
        $deserializer = new JSONDeserializer($this->dispatcher, $factory);
        $data = '{"mixedType": [0.5,1.5,"invalid value"]}';
        /**@var $fooBar FooBar */
        $fooBar = $deserializer->deserialize($data, 'a15l\serialization\deserializer\tests\fixtures\FooBar');
        $array = $fooBar->getMixedType();
        $this->assertCount(3, $array);
        $excpected = array(0.5, 1.5, 0.0);
        $this->assertSame($excpected, $array);


    }

    public function testWrongDateTimeFormat(){
        $factory = new MetadataFactory($this->metadataLoader, array(
            'types' => array(
                'mixedType' => array(
                    'DateTime' => 'Y-m-d'
                )
            )
        ));
        $deserializer = new JSONDeserializer($this->dispatcher, $factory);
        $data = '{"mixedType": "31-12-2015"}';
        /**@var $fooBar FooBar */
        $fooBar = $deserializer->deserialize($data, 'a15l\serialization\deserializer\tests\fixtures\FooBar');
        $this->assertNull($fooBar->getMixedType());
    }

    public function testEmptyDateTimeValue(){
        $factory = new MetadataFactory($this->metadataLoader, array(
            'types' => array(
                'mixedType' => array(
                    'DateTime' => 'Y-m-d'
                )
            )
        ));
        $deserializer = new JSONDeserializer($this->dispatcher, $factory);
        $data = '{"mixedType": ""}';
        /**@var $fooBar FooBar */
        $fooBar = $deserializer->deserialize($data, 'a15l\serialization\deserializer\tests\fixtures\FooBar');
        $this->assertNull($fooBar->getMixedType());
    }

    public function testDefaultEvent(){
        $factory = new MetadataFactory($this->metadataLoader, array(
            'default-deserialize-event' => 'append'
        ));
        $this->dispatcher->addListener(EventDispatcherInterface::EVENT_DESERIALIZE, 'append', function ($value){
            return $value . '.appended';
        });
        $deserializer = new JSONDeserializer($this->dispatcher, $factory);
        $data = '{"mixedType": "empty"}';
        /**@var $fooBar FooBar */
        $fooBar = $deserializer->deserialize($data, 'a15l\serialization\deserializer\tests\fixtures\FooBar');
        $this->assertEquals("empty.appended", $fooBar->getMixedType());
    }

    public function testArrayBooleanTypeCast(){
        $factory = new MetadataFactory($this->metadataLoader, array(
            'types' => array(
                'mixedType' => array(
                    'scalar-array' => 'boolean'
                )
            )
        ));
        $deserializer = new JSONDeserializer($this->dispatcher, $factory);
        $data = '{"mixedType": ["yes", "no", "true", false, "on", "off"]}';
        /**@var $fooBar FooBar */
        $fooBar = $deserializer->deserialize($data, 'a15l\serialization\deserializer\tests\fixtures\FooBar');
        $array = $fooBar->getMixedType();
        $this->assertCount(6, $array);
        $excpected = array(true, false, true, false, true, false);
        $this->assertSame($excpected, $array);


    }

    public function testArrayType(){
        $factory = new MetadataFactory($this->metadataLoader, array(
            'types' => array(
                'mixedType' => array(
                    'array' => ''
                )
            )
        ));
        $deserializer = new JSONDeserializer($this->dispatcher, $factory);
        $data = '{"mixedType": ["yes", "no", "true", false, "on", "off"]}';
        /**@var $fooBar FooBar */
        $fooBar = $deserializer->deserialize($data, 'a15l\serialization\deserializer\tests\fixtures\FooBar');
        $array = $fooBar->getMixedType();
        $this->assertCount(6, $array);
        $excpected = array("yes", "no", "true", false, "on", "off");
        $this->assertSame($excpected, $array);


    }

    public function testArrayCollectionType(){
        $factory = new MetadataFactory($this->metadataLoader, array(
            'types' => array(
                'mixedType' => array(
                    'array-collection' => 'a15l\serialization\deserializer\tests\fixtures\Bar'
                )
            )
        ));

        $deserializer = new JSONDeserializer($this->dispatcher, $factory);
        $data = '{"mixedType":[{"aliasName":"alias 1 value","aliasName2":"alias 2 value","scalarArray":[1,2,3,4]},'
                . '{"aliasName":"alias 1 value","aliasName2":"alias 2 value","scalarArray":[1,2,3,4]},'
                . '{"aliasName":"alias 1 value","aliasName2":"alias 2 value","scalarArray":[1,2,3,4]}]}';
        /**@var $fooBar FooBar */
        $fooBar = $deserializer->deserialize($data, 'a15l\serialization\deserializer\tests\fixtures\FooBar');
        $collection = $fooBar->getMixedType();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $collection);
        $this->assertCount(3, $collection);
    }

    public function testMalformedArray(){
        $factory = new MetadataFactory($this->metadataLoader);
        $deserializer = new JSONDeserializer($this->dispatcher, $factory);
        $data = '{"scalarArray": "invalid array", "fooValue":"some value.appended","fooDate":"2015-01-31","bar":{"'
                . 'aliasName":"alias 1 value","aliasName2":"alias 2 value","scalarArray":[1,2,3,4]},'
                . '"bars":[{"aliasName":"alias 1 value","aliasName2":"alias 2 value","scalarArray":[1,2,3,4]},'
                . '{"aliasName":"alias 1 value","aliasName2":"alias 2 value","scalarArray":[1,2,3,4]},'
                . '{"aliasName":"alias 1 value","aliasName2":"alias 2 value","scalarArray":[1,2,3,4]}]}';
        /**@var $foo Foo */
        $foo = $deserializer->deserialize($data, 'a15l\serialization\deserializer\tests\fixtures\Foo');
        $this->assertCount(0, $foo->getScalarArray());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMalformedJSON(){
        $factory = new MetadataFactory($this->metadataLoader);
        $deserializer = new JSONDeserializer($this->dispatcher, $factory);
        $deserializer->deserialize('', 'a15l\serialization\deserializer\tests\fixtures\Foo');
    }

    public function testFilter(){
        $htmlString = '<b>"HTML"</b>';
        $filter = function ($value){
            if (is_array($value)) {
                return $value;
            }
            return htmlspecialchars($value, ENT_QUOTES);
        };
        $escapedValue = $filter($htmlString);
        $factory = new MetadataFactory($this->metadataLoader);
        $deserializer = new JSONDeserializer($this->dispatcher, $factory);
        $deserializer->setFilter($filter);
        $data = '{"fooValue":"<b>\"HTML\"<\/b>","fooDate":"2015-01-31","bar":{"aliasName":'
                . '"alias 1 value","aliasName2":"alias 2 value","scalarArray":[1,2,3,4]},'
                . '"bars":[{"aliasName":"alias 1 value","aliasName2":"alias 2 value","scalarArray":[1,2,3,4]},'
                . '{"aliasName":"alias 1 value","aliasName2":"alias 2 value","scalarArray":[1,2,3,4]},'
                . '{"aliasName":"alias 1 value","aliasName2":"alias 2 value","scalarArray":[1,2,3,4]}]}';
        /**@var $foo Foo */
        $foo = $deserializer->deserialize($data, 'a15l\serialization\deserializer\tests\fixtures\Foo');
        $this->assertEquals($escapedValue, $foo->getFooValue());
    }

    public function testIgnoreAll(){
        $data = '{"fooBarValue" : "test", "DateTime" : "2015-03-19"}';
        $factory = new MetadataFactory($this->metadataLoader, array('ignore-all' => true));
        $deserializer = new JSONDeserializer($this->dispatcher, $factory);
        /**@var $fooBar \a15l\serialization\deserializer\tests\fixtures\FooBar */
        $fooBar = $deserializer->deserialize($data, 'a15l\serialization\deserializer\tests\fixtures\FooBar');
        $this->assertNull($fooBar->getDate());
        $this->assertNull($fooBar->getFooBarValue());
    }
}
