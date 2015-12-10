<?php

namespace phastl\compiler;

use phastl\exceptions\ParseException;

/**
 * Description of SectionParser
 *
 * @author Alexander Schlegel
 */
class SectionParser {

    private $sections = array();
    private $buffer;
    private $stack = array();
    private $sectionStartPattern;
    private $sectionEndPattern;

    public function __construct() {
        $this->sectionStartPattern = array(
            T_OPEN_TAG, T_VARIABLE, T_OBJECT_OPERATOR,
            T_STRING, '(', T_CONSTANT_ENCAPSED_STRING
        );
        $this->sectionEndPattern = array(
            T_OPEN_TAG, T_VARIABLE, T_OBJECT_OPERATOR, T_STRING, '(', ')',
            ';', T_CLOSE_TAG
        );
    }

    protected function checkIntegrity() {
        preg_match_all('#<\?php\s+\$this->section\((.*?)\)\s*;\s*\?>#ims', $this->buffer, $m);
        preg_match_all('#<\?php\s+\$this->end\(\)\s*;\s*\?>#ims', $this->buffer, $e);
        $start = count($m[0]);
        $end = count($e[0]);
        if ($start != $end) {
            throw new ParseException('The number of section start tags has '
            . 'to bee equals to the number of end tags. '
            . "Found $start start tags and $end end tags!");
        }
    }

    public function getSections($tplContent) {
        $this->buffer = $tplContent;
        $this->stack = array();
        $this->sections = array();
        $this->checkIntegrity();
        $buffer = "";
        $tokens = token_get_all($this->buffer);
        $tknCount = count($tokens) - 1;
        for ($i = 0; $i <= $tknCount; $i++) {
            $token = $tokens[$i];
            if (is_array($token)) {
                if (($sStart = $this->getSectionLastIndex($tokens, $i)) !== null) {
                    $startPos = strlen($buffer);
                    $sBuffer = "";
                    for ($x = $i; $x <= $sStart; $x++) {
                        if (is_array($tokens[$x])) {
                            $sBuffer .= $tokens[$x][1];
                        } else {
                            $sBuffer .= $tokens[$x];
                        }
                    }
                    $buffer .= $sBuffer;
                    $id = $this->stripQuotes($tokens[$i + 5][1]);
                    $this->sections[$id] = array(
                        'startPos' => $startPos,
                        'startContentPos' => $startPos + strlen($sBuffer),
                        'option' => (is_array($tokens[$i + 7]) ? $this->stripQuotes($tokens[$i + 7][1]) : 'append')
                    );
                    $this->stack[$id] = $id;
                    $i = $x - 1;
                    continue;
                }
                if (($sEnd = $this->getEndSectionLastIndex($tokens, $i)) !== null) {
                    $endPos = strlen($buffer);
                    $eBuffer = "";
                    for ($x = $i; $x <= $sEnd; $x++) {
                        if (is_array($tokens[$x])) {
                            $eBuffer .= $tokens[$x][1];
                        } else {
                            $eBuffer .= $tokens[$x];
                        }
                    }
                    $buffer .= $eBuffer;
                    $k = end($this->stack);
                    unset($this->stack[$k]);
                    $this->sections[$k]['endtPos'] = $endPos + strlen($eBuffer);
                    $this->sections[$k]['contentLength'] = $endPos - $this->sections[$k]['startContentPos'];
                    $i = $x - 1;
                    continue;
                }
                $buffer .= $token[1];
            } else {
                $buffer .= $token;
            }
        }
        return $this->sections;
    }

    protected function getEndSectionLastIndex($tokens, $i) {
        if (!is_array($tokens[$i])) {
            return;
        }
        if (!isset($tokens[$i + (count($this->sectionEndPattern) - 1)])) {
            return;
        }
        foreach ($this->sectionEndPattern as $k => $e) {
            $type = is_array($tokens[$i + $k]) ? $tokens[$i + $k][0] : $tokens[$i + $k];
            if ($type !== $e) {
                return;
            }
        }
        return $i + (count($this->sectionEndPattern) - 1);
    }

    protected function getSectionLastIndex($tokens, $i) {
        if (!is_array($tokens[$i])) {
            return;
        }
        // at least <?php $this->section("sectionid"); ? >
        //          ^0----^1---^2--^3---^4----^5---^6^7--^8
        if (!isset($tokens[$i + 8])) {
            return;
        }
        foreach ($this->sectionStartPattern as $k => $e) {
            $type = is_array($tokens[$i + $k]) ? $tokens[$i + $k][0] : $tokens[$i + $k];
            if ($type !== $e) {
                return;
            }
        }
        $end = $i + 8;
        // optional param
        if (!is_array($tokens[$i + 6]) && $tokens[$i + 6] === ',') {
            $end += 2;
        }
        return $end;
    }

    protected function stripQuotes($str) {
        return str_replace(array('"', "'"), '', $str);
    }

}
