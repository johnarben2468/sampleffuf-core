<?php


namespace a15l\serialization\deserializer\tests\fixtures;


class Bar{

    private $alias1;
    private $alias2;

    /**
     * Bar constructor.
     */
    public function __construct(){
    }


    /**
     * @return mixed
     */
    public function getAlias1(){
        return $this->alias1;
    }

    /**
     * @param mixed $alias1
     * @return Bar
     */
    public function setAlias1($alias1){
        $this->alias1 = $alias1;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAlias2(){
        return $this->alias2;
    }

    /**
     * @param mixed $alias2
     * @return Bar
     */
    public function setAlias2($alias2){
        $this->alias2 = $alias2;
        return $this;
    }


}