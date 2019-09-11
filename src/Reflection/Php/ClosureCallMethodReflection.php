<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Native\NativeParameterReflection;
use PHPStan\Reflection\PassedByReference;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ClosureType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\Type;

final class ClosureCallMethodReflection implements MethodReflection
{

	/** @var MethodReflection */
	private $nativeMethodReflection;

	/** @var ClosureType */
	private $closureType;

	public function __construct(
		MethodReflection $nativeMethodReflection,
		ClosureType $closureType
	)
	{
		$this->nativeMethodReflection = $nativeMethodReflection;
		$this->closureType = $closureType;
	}

	public function getDeclaringClass(): ClassReflection
	{
		return $this->nativeMethodReflection->getDeclaringClass();
	}

	public function isStatic(): bool
	{
		return $this->nativeMethodReflection->isStatic();
	}

	public function isPrivate(): bool
	{
		return $this->nativeMethodReflection->isPrivate();
	}

	public function isPublic(): bool
	{
		return $this->nativeMethodReflection->isPublic();
	}

	/** @return string|false */
	public function getDocComment()
	{
		return $this->nativeMethodReflection->getDocComment();
	}

	public function getName(): string
	{
		return $this->nativeMethodReflection->getName();
	}

	public function getPrototype(): ClassMemberReflection
	{
		return $this->nativeMethodReflection->getPrototype();
	}

	/**
	 * @return \PHPStan\Reflection\ParametersAcceptor[]
	 */
	public function getVariants(): array
	{
		$parameters = $this->closureType->getParameters();
		$newThis = new NativeParameterReflection(
			'newThis',
			false,
			new ObjectWithoutClassType(),
			PassedByReference::createNo(),
			false,
			null
		);

		array_unshift($parameters, $newThis);

		return [
			new FunctionVariant(
				$this->closureType->getTemplateTypeMap(),
				$this->closureType->getResolvedTemplateTypeMap(),
				$parameters,
				$this->closureType->isVariadic(),
				$this->closureType->getReturnType()
			),
		];
	}

	public function isDeprecated(): TrinaryLogic
	{
		return $this->nativeMethodReflection->isDeprecated();
	}

	public function getDeprecatedDescription(): ?string
	{
		return $this->nativeMethodReflection->getDeprecatedDescription();
	}

	public function isFinal(): TrinaryLogic
	{
		return $this->nativeMethodReflection->isFinal();
	}

	public function isInternal(): TrinaryLogic
	{
		return $this->nativeMethodReflection->isInternal();
	}

	public function getThrowType(): ?Type
	{
		return $this->nativeMethodReflection->getThrowType();
	}

	public function hasSideEffects(): TrinaryLogic
	{
		return $this->nativeMethodReflection->hasSideEffects();
	}

}
