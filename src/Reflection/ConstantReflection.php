<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\TrinaryLogic;

interface ConstantReflection extends ClassMemberReflection
{

	public function getName(): string;

	/**
	 * @return mixed
	 */
	public function getValue();

	public function isDeprecated(): TrinaryLogic;

	public function getDeprecatedDescription(): ?string;

	public function isInternal(): TrinaryLogic;

}
