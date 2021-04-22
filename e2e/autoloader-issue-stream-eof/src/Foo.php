<?php

namespace App;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class Foo extends ConstraintValidator
{
	/**
	 * @param mixed $value
	 * @param \Symfony\Component\Validator\Constraint $constraint
	 *
	 * @return void
	 */
	public function validate($value, Constraint $constraint): void
	{
		$this->context->buildViolation('foo');
	}
}

