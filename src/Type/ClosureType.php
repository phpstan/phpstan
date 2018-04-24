<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassConstantReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantIntegerType;

class ClosureType implements CompoundType, ParametersAcceptor
{

	/** @var ObjectType */
	private $objectType;

	/** @var Type */
	private $returnType;

	public function __construct(Type $returnType)
	{
		$this->objectType = new ObjectType(\Closure::class);
		$this->returnType = $returnType;
	}

	/**
	 * @return string[]
	 */
	public function getReferencedClasses(): array
	{
		return $this->objectType->getReferencedClasses();
	}

	public function accepts(Type $type): bool
	{
		return $this->objectType->accepts($type);
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		if ($type instanceof self) {
			return $this->returnType->isSuperTypeOf($type->returnType);
		}

		if ($type instanceof CompoundType) {
			return $type->isSubTypeOf($this);
		}

		return TrinaryLogic::createNo();
	}

	public function isSubTypeOf(Type $otherType): TrinaryLogic
	{
		if (
			$otherType instanceof self
			|| $otherType instanceof UnionType
			|| $otherType instanceof IntersectionType
		) {
			return $otherType->isSuperTypeOf($this);
		}

		$otherTypeCallable = $otherType->isCallable();
		if (!$otherTypeCallable->no()) {
			return $otherTypeCallable;
		}

		if ($otherType instanceof TypeWithClassName) {
			if ($otherType->getClassName() === \Closure::class) {
				return TrinaryLogic::createYes();
			}
		}

		return TrinaryLogic::createNo();
	}

	public function describe(VerbosityLevel $level): string
	{
		return sprintf('Closure<%s>', $this->returnType->describe($level));
	}

	public function canAccessProperties(): TrinaryLogic
	{
		return $this->objectType->canAccessProperties();
	}

	public function hasProperty(string $propertyName): bool
	{
		return $this->objectType->hasProperty($propertyName);
	}

	public function getProperty(string $propertyName, Scope $scope): PropertyReflection
	{
		return $this->objectType->getProperty($propertyName, $scope);
	}

	public function canCallMethods(): TrinaryLogic
	{
		return $this->objectType->canCallMethods();
	}

	public function hasMethod(string $methodName): bool
	{
		return $this->objectType->hasMethod($methodName);
	}

	public function getMethod(string $methodName, Scope $scope): MethodReflection
	{
		return $this->objectType->getMethod($methodName, $scope);
	}

	public function canAccessConstants(): TrinaryLogic
	{
		return $this->objectType->canAccessConstants();
	}

	public function hasConstant(string $constantName): bool
	{
		return $this->objectType->hasConstant($constantName);
	}

	public function getConstant(string $constantName): ClassConstantReflection
	{
		return $this->objectType->getConstant($constantName);
	}

	public function isIterable(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function getIterableKeyType(): Type
	{
		return new ErrorType();
	}

	public function getIterableValueType(): Type
	{
		return new ErrorType();
	}

	public function isOffsetAccessible(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function getOffsetValueType(Type $offsetType): Type
	{
		return new ErrorType();
	}

	public function setOffsetValueType(?Type $offsetType, Type $valueType): Type
	{
		return new ErrorType();
	}

	public function isCallable(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function getCallableParametersAcceptor(Scope $scope): ParametersAcceptor
	{
		return $this;
	}

	public function isCloneable(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function toBoolean(): BooleanType
	{
		return new ConstantBooleanType(true);
	}

	public function toNumber(): Type
	{
		return new ErrorType();
	}

	public function toInteger(): Type
	{
		return new ErrorType();
	}

	public function toFloat(): Type
	{
		return new ErrorType();
	}

	public function toString(): Type
	{
		return new ErrorType();
	}

	public function toArray(): Type
	{
		return new ConstantArrayType(
			[new ConstantIntegerType(0)],
			[$this],
			1
		);
	}

	/**
	 * @return \PHPStan\Reflection\ParameterReflection[]
	 */
	public function getParameters(): array
	{
		return [];
	}

	public function isVariadic(): bool
	{
		return true;
	}

	public function getReturnType(): Type
	{
		return $this->returnType;
	}

	/**
	 * @param mixed[] $properties
	 * @return Type
	 */
	public static function __set_state(array $properties): Type
	{
		return new self($properties['returnType']);
	}

}
