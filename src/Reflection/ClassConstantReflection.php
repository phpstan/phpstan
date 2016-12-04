<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

interface ClassConstantReflection extends ClassMemberReflection
{

	public function getName(): string;

	/**
	 * @return mixed
	 */
	public function getValue();

}
