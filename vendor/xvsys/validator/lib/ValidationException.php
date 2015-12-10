<?php

namespace xvsys\validator;

use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Description of ValidationException
 *
 * @author Alexander Schlegel
 */
class ValidationException extends \Exception implements ValidationExceptionInterface {

    /**
     *
     * @var ConstraintViolationListInterface 
     */
    private $violations;

    public function __construct(ConstraintViolationListInterface $violations) {
        $this->violations = $violations;
    }

    /**
     * 
     * @return ConstraintViolationListInterface
     */
    public function getViolations() {
        return $this->violations;
    }

}
