<?php


namespace a15l\serialization\deserializer\tests\fixtures;


class FooBar{

    private $fooBarValue;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var mixed
     */
    private $mixedType;

    /**
     * @return mixed
     */
    public function getFooBarValue(){
        return $this->fooBarValue;
    }

    /**
     * @param mixed $fooBarValue
     * @return FooBar
     */
    public function setFooBarValue($fooBarValue){
        $this->fooBarValue = $fooBarValue;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate(){
        return $this->date;
    }

    /**
     * @param \DateTime $date
     * @return FooBar
     */
    public function setDate($date){
        $this->date = $date;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMixedType(){
        return $this->mixedType;
    }

    /**
     * @param mixed $mixedType
     * @return FooBar
     */
    public function setMixedType($mixedType){
        $this->mixedType = $mixedType;
        return $this;
    }


}