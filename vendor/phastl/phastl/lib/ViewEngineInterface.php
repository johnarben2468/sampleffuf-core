<?php

namespace phastl;

/**
 *
 * @author Alexander Schlegel
 */
interface ViewEngineInterface {

    public function setDynamicLayout($tplFile);

    public function assign($name, $value);

    public function assignVars(array $vars);

    public function render($tplFile, array $vars = array());
}
