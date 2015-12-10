<?php

namespace yapcdi\converter;

/**
 * Description of AbstractGenerator
 *
 * @author Alexander Schlegel
 */
abstract class AbstractConverter {

    public function __construct() {
        
    }

    public function getPHPClasses($dirs = array()) {
        $classes = array();
        foreach ($dirs as $dir) {
            $classes = array_merge($classes, $this->scanDirForClasses($dir));
        }
        return $classes;
    }

    protected function scanDirForClasses($dir) {
        $classes = array();
        $d = new \DirectoryIterator($dir);
        foreach ($d as $file) {
            if ($file->isDot()) {
                continue;
            }
            if ($file->isDir()) {
                $classes = array_merge($classes, $this->scanDirForClasses($file->getRealPath()));
                continue;
            }
            if (strtolower($file->getExtension()) !== 'php') {
                continue;
            }
            $clss = $this->getClasses($file->getRealPath());
            if (count($clss) === 0) {
                continue;
            }
            $classes = array_merge($classes, $clss);
        }
        return $classes;
    }

    public function getClasses($file) {
        $classes = array();
        if (!file_exists($file)) {
            throw new \ErrorException('File ' . $file . ' not found');
        }
        $content = php_strip_whitespace($file);
        if ($content == "" || ($strpos = strpos($content, '{')) === false) {
            return $classes;
        }
        $namespace = $class = "";
        preg_match('/namespace (.*?)\;/si', $content, $m);
        if (count($m) > 0) {
            $namespace = trim($m[1]);
        }
        preg_match_all('/class (.*?)[\s\{]/si', $content, $c);
        foreach ($c[1] as $className) {
            $classes[] = $namespace . '\\' . trim($className);
        }
        return $classes;
    }

}
