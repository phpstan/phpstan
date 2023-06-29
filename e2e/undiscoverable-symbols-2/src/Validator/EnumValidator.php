<?php

declare(strict_types=1);

namespace App\Validator;

use MyCLabs\Enum\Enum as ClabsEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class EnumValidator extends ConstraintValidator
{
    /**
     * @param mixed $value
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Enum) {
            throw new UnexpectedTypeException($constraint, Enum::class); // @codeCoverageIgnore
        }

        if (null === $value) {
            return;
        }

        if ($value instanceof \BackedEnum) {
            $value = $constraint->checkKey ? $value->name : $value->value;
        }

        assert(is_int($value) || is_string($value));

        if (is_subclass_of($constraint->class, ClabsEnum::class)) {
            /** @var class-string<ClabsEnum<int>>|class-string<ClabsEnum<string>> $enumClass */
            $enumClass = $constraint->class;
            $valid = $constraint->checkKey ? $enumClass::isValidKey($value) : $enumClass::isValid($value);
        } else {
            /** @var class-string<\BackedEnum> $enumClass */
            $enumClass = $constraint->class;
            $validValues = $constraint->checkKey
                ? array_column($enumClass::cases(), 'name')
                : array_column($enumClass::cases(), 'value');
            $valid = in_array($value, $validValues, true);
        }

        if (!$valid) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', (string) $value)
                ->setCode(Enum::ENUM)
                ->addViolation();
        }
    }
}
