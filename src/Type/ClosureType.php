<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ConstantReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantIntegerType;

class ClosureType implements Type, ParametersAcceptor
{

	/** @var ObjectType */
	private $objectType;

	/** @var array<int, \PHPStan\Reflection\Native\NativeParameterReflection> */
	private $parameters;

	/** @var Type */
	private $returnType;

	/** @var bool */
	private $variadic;

	/**
	 * @param array<int, \PHPStan\Reflection\Native\NativeParameterReflection> $parameters
	 * @param Type $returnType
	 * @param bool $variadic
	 */
	public function __construct(
		array $parameters,
		Type $returnType,
		bool $variadic
	)
	{
		$this->objectType = new ObjectType(\Closure::class);
		$this->parameters = $parameters;
		$this->returnType = $returnType;
		$this->variadic = $variadic;
	}

	/**
	 * @return string[]
	 */
	public function getReferencedClasses(): array
	{
		return $this->objectType->getReferencedClasses();
	}

	public function accepts(Type $type, bool $strictTypes): TrinaryLogic
	{
		if (!$type instanceof ClosureType) {
			return $this->objectType->accepts($type, $strictTypes);
		}

		$theirParameters = $type->parameters;
		$ourParameters = $this->parameters;
		if (count($theirParameters) > count($ourParameters)) {
			return TrinaryLogic::createNo();
		}

		$result = null;
		foreach ($theirParameters as $i => $theirParameter) {
			$ourParameter = $ourParameters[$i];
			$isSuperType = $theirParameter->getType()->isSuperTypeOf($ourParameter->getType());
			if ($result === null) {
				$result = $isSuperType;
			} else {
				$result = $result->and($isSuperType);
			}
		}

		$isReturnTypeSuperType = $this->returnType->isSuperTypeOf($type->returnType);
		if ($result === null) {
			$result = $isReturnTypeSuperType;
		} else {
			$result = $result->and($isReturnTypeSuperType);
		}

		return $result;
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		if ($type instanceof self) {
			return $this->returnType->isSuperTypeOf($type->returnType);
		}

		if ($type instanceof CompoundType) {
			return $type->isSubTypeOf($this);
		}

		if ($type instanceof ObjectWithoutClassType) {
			return TrinaryLogic::createMaybe();
		}

		if (
			$type instanceof TypeWithClassName
			&& $type->getClassName() === \Closure::class
		) {
			return TrinaryLogic::createMaybe();
		}

		return TrinaryLogic::createNo();
	}

	public function equals(Type $type): bool
	{
		if (!$type instanceof self) {
			return false;
		}

		return $this->returnType->equals($type->returnType);
	}

	public function describe(VerbosityLevel $level): string
	{
		return sprintf(
			'Closure(%s): %s',
			implode(', ', array_map(function (ParameterReflection $parameter) use ($level): string {
				return $parameter->getType()->describe($level);
			}, $this->parameters)),
			$this->returnType->describe($level)
		);
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

	public function getConstant(string $constantName): ConstantReflection
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

	public function hasOffsetValueType(Type $offsetType): TrinaryLogic
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

	/**
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return \PHPStan\Reflection\ParametersAcceptor[]
	 */
	public function getCallableParametersAcceptors(Scope $scope): array
	{
		return [$this];
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
	 * @return array<int, \PHPStan\Reflection\Native\NativeParameterReflection>
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

	public function isVariadic(): bool
	{
		return $this->variadic;
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
		return new self(
			$properties['parameters'],
			$properties['returnType'],
			$properties['variadic']
		);
	}

}
