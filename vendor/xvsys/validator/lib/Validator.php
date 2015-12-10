<?php

namespace xvsys\validator;

use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Description of Validator
 *
 * @author Alexander Schlegel
 */
class Validator {

    /**
     *
     * @var ValidatorInterface 
     */
    private $validator;

    public function __construct(ValidatorInterface $validator) {
        $this->validator = $validator;
    }

    /**
     * 
     * @param object $object
     * @throws ValidationException
     */
    public function validate($object) {
        $violations = $this->validator->validate($object);
        if (count($violations) > 0) {
            throw new ValidationException($violations);
        }
    }

    /**
     * 
     * @return ValidatorInterface
     */
    public function getValidator() {
        return $this->validator;
    }

}
