<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;

interface FunctionReflection
{

	public function getName(): string;

	/**
	 * @return \PHPStan\Reflection\ParametersAcceptor[]
	 */
	public function getVariants(): array;

	public function isDeprecated(): TrinaryLogic;

	public function getDeprecatedDescription(): ?string;

	public function isFinal(): TrinaryLogic;

	public function isInternal(): TrinaryLogic;

	public function getThrowType(): ?Type;

}
