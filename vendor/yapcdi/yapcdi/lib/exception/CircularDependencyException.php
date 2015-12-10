<?php

namespace yapcdi\exception;

/**
 * Description of CycleException
 *
 * @author Alexander Schlegel
 */
class CircularDependencyException extends \Exception {

    public function __construct($message = null, $code = null, $previous = null) {
        parent::__construct($message, $code, $previous);
    }

}
