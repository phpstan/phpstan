<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;

interface MethodReflection extends ClassMemberReflection
{

	public function getName(): string;

	public function getPrototype(): ClassMemberReflection;

	/**
	 * @return \PHPStan\Reflection\ParametersAcceptor[]
	 */
	public function getVariants(): array;

	public function isDeprecated(): TrinaryLogic;

	public function getDeprecatedDescription(): ?string;

	public function isFinal(): TrinaryLogic;

	public function isInternal(): TrinaryLogic;

	public function getThrowType(): ?Type;

	public function hasSideEffects(): TrinaryLogic;

}
