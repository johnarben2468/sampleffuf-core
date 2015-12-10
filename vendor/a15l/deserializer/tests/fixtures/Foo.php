<?php


namespace a15l\serialization\deserializer\tests\fixtures;


class Foo{

    private $ignored;
    private $fooValue;

    /**
     * @var \DateTime
     */
    private $fooDate;

    /**
     * @var Bar
     */
    private $bar;

    /**
     * @var Bar[]
     */
    private $bars = array();

    /**
     * @var array int
     */
    private $scalarArray = array();

    /**
     * @return mixed
     */
    public function getIgnored(){
        return $this->ignored;
    }

    /**
     * @param mixed $ignored
     * @return Foo
     */
    public function setIgnored($ignored){
        $this->ignored = $ignored;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFooValue(){
        return $this->fooValue;
    }

    /**
     * @param mixed $fooValue
     * @return Foo
     */
    public function setFooValue($fooValue){
        $this->fooValue = $fooValue;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getFooDate(){
        return $this->fooDate;
    }

    /**
     * @param \DateTime $fooDate
     * @return Foo
     */
    public function setFooDate($fooDate){
        $this->fooDate = $fooDate;
        return $this;
    }

    /**
     * @return Bar
     */
    public function getBar(){
        return $this->bar;
    }

    /**
     * @param Bar $bar
     * @return Foo
     */
    public function setBar($bar){
        $this->bar = $bar;
        return $this;
    }

    /**
     * @return Bar[]
     */
    public function getBars(){
        return $this->bars;
    }

    /**
     * @param Bar[] $bars
     * @return Foo
     */
    public function setBars($bars){
        $this->bars = $bars;
        return $this;
    }

    /**
     * @return array int
     */
    public function getScalarArray(){
        return $this->scalarArray;
    }

    /**
     * @param array $scalarArray int
     * @return Foo
     */
    public function setScalarArray(array $scalarArray){
        $this->scalarArray = $scalarArray;
        return $this;
    }


}