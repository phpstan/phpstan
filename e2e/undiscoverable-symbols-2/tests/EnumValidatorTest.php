<?php

declare(strict_types=1);

namespace App\Tests\Unit\Validator;

use App\Security\Authorization\Role;
use App\Validator\Enum;
use App\Validator\EnumValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class EnumValidatorTest extends ConstraintValidatorTestCase
{
	protected function createValidator(): EnumValidator
	{
		return new EnumValidator();
	}

	public function testValid(): void
	{
		$constraint = new Enum(
			[
				'class' => Role::class,
				'message' => 'myMessage',
			]
		);

		$this->validator->validate(Role::ADMIN->value, $constraint);
		$this->assertNoViolation();
	}

	public function testInvalid(): void
	{
		$constraint = new Enum(
			[
				'class' => Role::class,
				'message' => 'myMessage',
			]
		);

		$value = 'knight';
		$this->validator->validate($value, $constraint);

		$this->buildViolation('myMessage')
			->setParameter('{{ value }}', $value)
			->setCode(Enum::ENUM)
			->assertRaised();
	}
}
