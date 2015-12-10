<?php

namespace phastl;

/**
 * Description of ViewEngine
 *
 * @author Alexander Schlegel
 */
class ViewEngine implements ViewEngineInterface {

    private $vars = array();
    private $tplDir;
    private $compileDir;
    private $suffix;
    private $layout;

    public function __construct($tplDir, $compileDir = null, $suffix = '.php') {
        $this->tplDir = $tplDir;
        $this->compileDir = $compileDir;
        $this->suffix = $suffix;
    }

    public function assign($name, $value) {
        $this->vars[$name] = $value;
        return $this;
    }

    public function assignVars(array $vars) {
        foreach ($vars as $k => $v) {
            $this->vars[$k] = $v;
        }
        return $this;
    }

    public function render($tplFile, array $vars = array()) {
        $file = $this->tplDir . DIRECTORY_SEPARATOR . $tplFile . $this->suffix;
        if ($this->compileDir !== null) {
            $cTplFile = str_replace(array('/', '\\'), '.', $file);
            if ($this->layout !== null) {
                $cTplFile = str_replace(array('/', '\\'), '.', $this->layout
                        ) . '#' . $cTplFile;
            }
            $cFile = $this->compileDir . DIRECTORY_SEPARATOR . $cTplFile;
            if (!file_exists($cFile)) {
                $this->compile($tplFile, $cTplFile);
            }
            ob_start();
            extract(array_merge($vars, $this->vars));
            require $cFile;
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        }
        $t = new Template($tplFile, $this->tplDir, $this->suffix);
        $t->setLayout($this->layout);
        $t->assignVars($vars);
        $t->assignVars($this->vars);
        return $t->render();
    }

    private function compile($tplFile, $cName) {
        $c = new compiler\Compiler($tplFile, $this->tplDir, $this->suffix);
        $c->setLayout($this->layout);
        $tmpFile = tempnam($this->compileDir, 'tpl');
        if (!($fh = @fopen($tmpFile, 'wb'))) {
            $tmpFile = $this->compileDir . DIRECTORY_SEPARATOR . uniqid('wrt');
            if (!($fh = @fopen($tmpFile, 'wb'))) {
                throw new exceptions\CompilerException("Could not open temporary file!");
            }
        }
        fwrite($fh, $c->getCompiled());
        fclose($fh);
        $cFile = $this->compileDir . DIRECTORY_SEPARATOR . $cName;
        if (DIRECTORY_SEPARATOR == '\\' || !@rename($tmpFile, $cFile)) {
            @unlink($cFile);
            @rename($tmpFile, $cFile);
        }
    }

    public function setDynamicLayout($tplFile) {
        $this->layout = $tplFile;
    }

}
