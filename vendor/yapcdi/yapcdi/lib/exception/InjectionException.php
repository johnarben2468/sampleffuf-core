<?php

namespace yapcdi\exception;

/**
 * Description of InjectionException
 *
 * @author Alexander Schlegel
 */
class InjectionException extends \Exception {

    const CLASS_NOT_INSTANTIABLE = 19891901;
    const MISSING_REQUIRED_PARAM = 19891902;

    public function __construct($message = null, $code = null, $previous = null) {
        parent::__construct($message, $code, $previous);
    }

}
