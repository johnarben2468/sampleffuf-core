<?php

namespace yapcdi\converter;

/**
 *
 * @author Alexander Schlegel
 */
interface ConverterInterface {

    public function getConfig();

    public function getConvertedConfig();
}
