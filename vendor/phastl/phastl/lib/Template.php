<?php

namespace phastl;

/**
 * Description of Template
 *
 * @author Alexander Schlegel
 */
class Template implements TemplateInterface {

    private $vars = array();
    private $sections = array();
    private $tplFile;
    private $tplDir;
    private $layout;
    private $child;
    private $noLayout = false;
    private $buffer;
    private $stack = array();
    private $suffix;

    public function __construct($tplFile, $tplDir, $suffix = '.php', $noLayout = false, $child = null) {
        $this->tplFile = $tplFile;
        $this->tplDir = $tplDir;
        $this->noLayout = $noLayout;
        $this->child = $child;
        $this->suffix = $suffix;
    }

    public function assign($name, $value) {
        $this->vars[$name] = $value;
    }

    public function assignVars(array $vars) {
        foreach ($vars as $k => $v) {
            $this->vars[$k] = $v;
        }
    }

    public function getChild() {
        return $this->child;
    }

    public function getSections() {
        return $this->sections;
    }

    public function insert($tplFile) {
        $t = new Template($tplFile, $this->tplDir, $this->suffix, true);
        $t->assignVars($this->vars);
        return $t->render();
    }

    public function render() {
        ob_start();
        extract($this->vars);
        require $this->tplDir . DIRECTORY_SEPARATOR . $this->tplFile . '.php';
        $content = ob_get_contents();
        $this->buffer = $content;
        ob_end_clean();
        $this->mergeSections();
        if ($this->layout !== null) {
            $t = new Template($this->layout, $this->tplDir, $this->suffix, false, $this);
            $t->assignVars($this->vars);
            return $t->render();
        }
        return $content;
    }

    protected function mergeSections() {
        if ($this->child !== null) {
            $childSections = $this->child->getSections();
            foreach ($childSections as $k => $s) {
                if (!isset($this->sections[$k])) {
                    $this->sections[$k] = $s;
                }
            }
        }
    }

    public function section($name, $option = 'append') {
        $this->sections[$name] = array(
            'content' => '',
            'option' => $option
        );
        $this->stack[$name] = $name;
        ob_start();
    }

    public function end() {
        $id = end($this->stack);
        $this->sections[$id]['content'] = ob_get_contents();
        if ($this->child !== null) {
            $childSections = $this->child->getSections();
            if (isset($childSections[$id])) {
                $s = $childSections[$id];
                switch ($s['option']) {
                    case 'replace':
                        $this->sections[$id]['content'] = $s['content'];
                        break;
                    case 'prepand':
                        $this->sections[$id]['content'] = $s['content'] . $this->sections[$id]['content'];
                        break;
                    case 'append':
                    default:
                        $this->sections[$id]['content'] .= $s['content'];
                }
            }
        }
        if (strlen($this->sections[$id]['content']) > 0) {
            ob_clean();
        }
        if ($this->layout === null) {
            echo $this->sections[$id]['content'];
        }
        ob_end_flush();
        unset($this->stack[$id]);
    }

    public function setLayout($layout) {
        if ($this->noLayout === false) {
            $this->layout = $layout;
        }
    }

    public function getBuffer() {
        return $this->buffer;
    }

    public function getChildBuffer() {
        if ($this->child !== null) {
            return $this->child->getBuffer();
        }
    }

    public function beginAssign() {
        
    }

    public function endAssign() {
        
    }

}
