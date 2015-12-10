<?php

namespace yapcdi\tests\converter;

/**
 * Description of PHPFileConverterTest
 *
 * @author Alexander Schlegel
 */
class PHPFileConverterTest extends \PHPUnit_Framework_TestCase {

    private $expectedClasses = array(
        'yapcdi\tests\data\B', 'yapcdi\tests\data\A', 'yapcdi\tests\data\C',
        'yapcdi\tests\data\SetterMetadataError'
    );
    private $configFileHash = '38f71c4729fd901c531fdfa31daa4ac2';

    public function setUp() {
        require_once 'tests/data/TestClasses.php';
    }

    public function testConverter() {
        $c = new \yapcdi\converter\MetadataToPHPFile();
        $classes = $c->getClasses('tests/data/TestClasses.php');
        $c->generate($classes);
        $this->assertEquals($this->configFileHash, md5($c->getConvertedConfig()));
        $config = $c->getConfig();
        // The classes B, A, C has valid metadata configuration
        // so the metadata can be converted
        // 
        // SetterMetadataError has also a valid metadata configuration but only 
        // if the flag "useNullAsDefaultValue" is set to true
        // 
        // although the class E has a valid metadata syntax, 
        // the converter ignores it, because no parameters of the constructor 
        // has been overwrriden
        //
        // Check the expected classes in the generated config
        foreach ($this->expectedClasses as $c) {
            $this->assertArrayHasKey($c, $config);
        }
    }

    /**
     * @expectedException \yapcdi\exception\AnnotationException
     */
    public function testAnnotationException() {
        $c = new \yapcdi\converter\MetadataToPHPFile(true, false);
        $classes = $c->getClasses('tests/data/TestClasses.php');
        $c->generate($classes);
        $this->fail();
    }

    /**
     * @expectedException \ErrorException
     */
    public function testFileNotFoundException() {
        $c = new \yapcdi\converter\MetadataToPHPFile(true, false);
        $classes = $c->getClasses('#non#existing-#file.extension');
        $c->generate($classes);
        $this->fail();
    }

    public function testDirScan() {
        $c = new \yapcdi\converter\MetadataToPHPFile();
        $c->generate($c->getPHPClasses(array('tests/data')));
        $this->assertEquals($this->configFileHash, md5($c->getConvertedConfig()));
        $config = $c->getConfig();
        // Check the expected classes in the generated config
        foreach ($this->expectedClasses as $c) {
            $this->assertArrayHasKey($c, $config);
        }
    }

}
