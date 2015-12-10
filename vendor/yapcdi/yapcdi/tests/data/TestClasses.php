<?php

/*
 * The MIT License
 *
 * Copyright 2015 Alexander Schlegel.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace yapcdi\tests\data;

interface DInterface {
    
}

interface BInterface {
    
}

interface YInterface {
    
}

class Y implements YInterface {
    
}

interface XInterface {
    
}

class X implements XInterface {
    
}

class X2 implements XInterface {
    
}

class Z {
    
}

Class B implements BInterface {

    /**
     *
     * @Inject("\yapcdi\tests\data\X")
     * @var XInterface 
     */
    private $x;

    /**
     * @Inject
     * @var \yapcdi\tests\data\YInterface
     */
    private $y;

    /**
     * @Inject
     * @var \yapcdi\tests\data\Z
     */
    private $z;

    public function __construct() {
        
    }

    /**
     * 
     * @return XInterface|null
     */
    public function getX() {
        return $this->x;
    }

    public function getY() {
        return $this->y;
    }

    public function getZ() {
        return $this->z;
    }

    public function setX(XInterface $x) {
        $this->x = $x;
    }

    public function setY(YInterface $y) {
        $this->y = $y;
    }

    public function setZ(Z $z) {
        $this->z = $z;
    }

}

class F {
    
}

class G {
    
}

class D implements DInterface {
    
}

class E {

    private $g;

    /**
     * @Inject
     * @param G $g
     */
    public function __construct(G $g) {
        $this->g = $g;
    }

    public function getG() {
        return $this->g;
    }

}

class A {

    private $b;
    private $c;
    private $d;

    /**
     * @INjeCt("b", "\yapcdi\tests\data\B")
     * @param BInterface $b
     * @param C $c
     */
    public function __construct(BInterface $b, C $c) {
        $this->b = $b;
        $this->c = $c;
    }

    /**
     * @inject("d", "\yapcdi\tests\data\D")
     * @param DInterface $d
     */
    public function setD(DInterface $d) {
        $this->d = $d;
    }

    /**
     * @Inject
     */
    public function setterWithNoParams() {
        
    }

    public function getB() {
        return $this->b;
    }

    public function getC() {
        return $this->c;
    }

    public function getD() {
        return $this->d;
    }

}

class C {

    private $e;
    private $f;
    private $foo;
    private $bar;

    /**
     * 
     * @Inject("foo", "fOo")
     * @param E $e
     * @param F $f
     */
    public function __construct(E $e, F $f, $foo, $bar = 'bar') {
        $this->e = $e;
        $this->f = $f;
        $this->foo = $foo;
        $this->bar = $bar;
    }

    public function getE() {
        return $this->e;
    }

    public function getF() {
        return $this->f;
    }

    public function getFoo() {
        return $this->foo;
    }

    public function getBar() {
        return $this->bar;
    }

}

class PropertyMetadataError {

    /**
     * @Inject("foo", "bar")
     */
    public $foo;

}

class PropertyMetadataError2 {

    /**
     * @Inject
     */
    public $foo;

}

class SetterMetadataError {

    /**
     * @Inject
     */
    public function foo($bar, SetterMetadataError $foo) {
        
    }

}

class SetterMetadataError2 {

    /**
     * @Inject("SetterMetadataError")
     */
    public function foo($bar) {
        
    }

}

class CircularDependency {

    private $d;

    public function __construct(CircularDependency $d) {
        $this->d = $d;
    }

}
