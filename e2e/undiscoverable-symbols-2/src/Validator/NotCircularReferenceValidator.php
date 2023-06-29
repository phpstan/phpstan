<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NotCircularReferenceValidator extends ConstraintValidator
{
    /**
     * @var array<string,bool>
     */
    private array $circularReferences;

    public function __construct(private PropertyAccessorInterface $propertyAccess)
    {
    }

	/**
	 * @param mixed $value
	 */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof NotCircularReference) {
            throw new UnexpectedTypeException($constraint, NotCircularReference::class); // @codeCoverageIgnore
        }

        $this->circularReferences = [];

        while (true) {
            if (!is_object($value)) {
                break;
            }
            if ($this->isCircularReference($value)) {
                $this->context->buildViolation($constraint->message)
                    ->setCode(NotCircularReference::NOT_CIRCULAR_REFERENCE)
                    ->addViolation();
                break;
            }
            $value = $this->propertyAccess->getValue($value, $constraint->propertyName);
        }
    }

    protected function isCircularReference(object $object): bool
    {
        $objectHash = spl_object_hash($object);

        if (isset($this->circularReferences[$objectHash])) {
            return true;
        }

        $this->circularReferences[$objectHash] = true;

        return false;
    }
}
