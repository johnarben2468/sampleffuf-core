<?php

namespace yapcdi\exception;

/**
 * Description of AnnotationException
 *
 * @author Alexander Schlegel
 */
class AnnotationException extends \Exception {

    public function __construct($message = null, $code = null, $previous = null) {
        parent::__construct($message, $code, $previous);
    }

}
