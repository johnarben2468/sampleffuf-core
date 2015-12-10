<?php

namespace phastl;

/**
 *
 * @author Alexander Schlegel
 */
interface TemplateInterface {

    public function section($name, $option = 'append');

    public function end();

    public function setLayout($layout);

    public function insert($tplFile);

    public function assign($name, $value);

    public function beginAssign();

    public function endAssign();

    public function render();

    public function assignVars(array $vars);

    public function getSections();

    /**
     * @return TemplateInterface
     */
    public function getChild();

    public function getChildBuffer();

    public function getBuffer();
}
