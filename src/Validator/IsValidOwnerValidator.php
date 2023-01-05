<?php

namespace App\Validator;

use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsValidOwnerValidator extends ConstraintValidator
{

    public function __construct(private Security $security)
    {
    }

    public function validate($value, Constraint $constraint)
    {
        /* @var App\Validator\IsValidOwner $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        // TODO: implement the validation here
        // Only allow this validator on property which is a User Obj.
        $user = $this->security->getUser();
        if (!$user instanceof User){
            $this->context->buildViolation($constraint->anonymousMessage)
                ->addViolation();

            return;
        }

        // check for Admin User
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        if (!$value instanceof User){
            throw new \InvalidArgumentException('@IsValidOwner constraint must be put on a property containing a User object');
        }

        if ($value->getId() !== $user->getId()) {
            $this->context->buildViolation($constraint->message)
            // ->setParameter('{{ value }}', $value)
            ->addViolation();
        }

    }
}
