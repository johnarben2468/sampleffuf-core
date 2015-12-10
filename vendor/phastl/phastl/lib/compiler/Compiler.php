<?php

namespace phastl\compiler;

/**
 * Description of Parser
 *
 * @author Alexander Schlegel
 */
class Compiler {

    private $buffer;
    private $layout;
    private $tplDir;
    private $sections = array();
    private $assigBlocks;
    private $child;
    private $noLayout;
    private $sParser;
    private $suffix;
    private $file;

    public function __construct($file, $tplDir, $suffix = '.php', $noLayout = false, $child = null) {
        $this->file = $tplDir . DIRECTORY_SEPARATOR . $file . $suffix;
        $this->loadFile($this->file);
        $this->suffix = $suffix;
        $this->tplDir = $tplDir;
        $this->noLayout = $noLayout;
        $this->child = $child;
        $this->sParser = new SectionParser();
    }

    public function setLayout($layout) {
        $this->layout = $layout;
    }

    public function getSections() {
        return $this->sections;
    }

    public function getAssigBlocks() {
        $blocks = "";
        if ($this->child !== null) {
            $blocks .= $this->child->getAssigBlocks();
        }
        $blocks .= $this->assigBlocks;
        return $blocks;
    }

    protected function loadFile($file) {
        $striped = "";
        // remove all whitespaces and comments
        // replace short tags
        $t = token_get_all(str_replace('<?=', '<?php echo ', php_strip_whitespace($file)));
        $blacklist = array(T_AS, T_CASE, T_INSTANCEOF, T_USE);
        foreach ($t as $i => $token) {
            if (is_string($token)) {
                $striped .= $token;
            } elseif (T_WHITESPACE === $token[0]) {
                if (isset($t[$i + 1]) && is_array($t[$i + 1])) {
                    if (in_array($t[$i + 1][0], $blacklist)) {
                        $striped .= $t[$i][1];
                        continue;
                    }
                    if (isset($t[$i - 1]) && is_array($t[$i - 1])) {
                        if (in_array($t[$i - 1][0], $blacklist)) {
                            $striped .= $t[$i][1];
                            continue;
                        }
                        if ($t[$i - 1][0] == T_ECHO || $t[$i - 1][0] == T_PRINT) {
                            $striped .= $t[$i][1];
                            continue;
                        }
                    }
                }
                $striped .= str_replace(' ', '', $token[1]);
            } else {
                $striped .= $token[1];
            }
        }
        $this->buffer = $striped;
    }

    protected function parseSections() {
        $sections = $this->sParser->getSections($this->buffer);
        // load sections
        foreach ($sections as $id => $s) {
            $this->sections[$id] = array(
                'content' => substr($this->buffer, $s['startContentPos'], $s['contentLength']),
                'option' => $s['option']
            );
        }
    }

    protected function mergeSections() {
        if ($this->child !== null) {
            $childSections = $this->child->getSections();
            foreach ($childSections as $id => $s) {
                if (isset($this->sections[$id])) {
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
                            break;
                    }
                    $pos = $this->sParser->getSections($this->buffer);
                    $sPos = $pos[$id];
                    $this->buffer = substr_replace($this->buffer, $this->sections[$id]['content'], $sPos['startContentPos'], $sPos['contentLength']);
                } else {
                    $this->sections[$id] = $s;
                }
            }
        }
        // clean buffer
        $bufferSection = $this->sParser->getSections($this->buffer);
        foreach ($bufferSection as $id => $options) {
            $p = $this->sParser->getSections($this->buffer);
            $s = $p[$id];
            if ($this->layout === null) {
                $this->buffer = substr_replace($this->buffer, $this->sections[$id]['content'], $s['startPos'], $s['endtPos'] - $s['startPos']);
            } else {
                $this->buffer = substr_replace($this->buffer, '', $s['startPos'], $s['endtPos'] - $s['startPos']);
            }
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

    protected function parseBuffer() {
        preg_match_all('#<\?php\s+echo\s*\$this->getChildBuffer\(\)\s*;\s*\?>#ims', $this->buffer, $m);
        foreach ($m[0] as $ins) {
            $pos = strpos($this->buffer, $ins);
            $this->buffer = substr_replace($this->buffer, $this->getChildBuffer(), $pos, strlen($ins));
        }
    }

    public function getCompiled() {
        try {
            $this->parseLayout();
            $this->parseInserts();
            $this->parseAssignBlocks();
            $this->parseBuffer();
            $this->parseSections();
            $this->mergeSections();
            if ($this->layout !== null) {
                $tpl = new Compiler($this->layout, $this->tplDir, $this->suffix, false, $this);
                return $tpl->getCompiled();
            }
            return trim($this->getAssigBlocks()) . $this->buffer;
        } catch (\phastl\exceptions\ParseException $ex) {
            throw new \phastl\exceptions\CompilerException(
            'Template: "' . $this->file . ' colud not be compiled! ' .
            $ex->getMessage()
            );
        }
    }

    protected function parseInserts() {
        preg_match_all('#<\?php\s+(echo|print)\s+\$this->insert\((.*?)\)\s*;\s*\?>#ims', $this->buffer, $m);
        foreach ($m[0] as $k => $i) {
            $sub = new Compiler($this->stripQuotes($m[2][$k]), $this->tplDir, $this->suffix, true);
            $this->buffer = str_replace($i, $sub->getCompiled(), $this->buffer);
        }
    }

    protected function parseAssignBlocks() {
        preg_match_all('#\$this->assign\((.*?)\s*,\s*(.*?)\)\s*;#ims', $this->buffer, $m);
        foreach ($m[0] as $k => $a) {
            $varName = $this->stripQuotes($m[1][$k]);
            $this->buffer = str_replace($a, '$' . $varName . '=' . $m[2][$k] . ';', $this->buffer);
        }
        preg_match_all('#\$this->assignVars\((.*?)\)\s*;#ims', $this->buffer, $blocks);
        foreach ($blocks[0] as $k => $block) {
            $vars = $blocks[1][$k];
            $this->buffer = str_replace($block, 'extract(' . $vars . ');', $this->buffer);
        }
        preg_match_all('#<\?php\s+\$this->beginAssign\(\)\s*;\s*\?>(.*?)<\?php\s+\$this->endAssign\(\)\s*;\s*\?>#ims', $this->buffer, $m2);
        foreach ($m2[0] as $k => $aMatch) {
            $this->assigBlocks .= $m2[1][$k];
            $this->buffer = str_replace($aMatch, '', $this->buffer);
        }
    }

    protected function parseLayout() {
        preg_match_all('#<\?php\s+\$this->setLayout\((.*?)\)\s*;\s*\?>#ims', $this->buffer, $m);
        foreach ($m[0] as $k => $block) {
            $this->buffer = str_replace($block, '', $this->buffer);
        }
        if ($this->noLayout === true) {
            return;
        }
        if (count($m[0]) > 0) {
            // no layout
            if (strtolower($m[1][0]) == 'null') {
                $this->layout = null;
                $this->noLayout = true;
                return;
            }
            $this->layout = $this->stripQuotes($m[1][0]);
        }
    }

    protected function stripQuotes($str) {
        return str_replace(array('"', "'"), '', $str);
    }

}
