<?php declare(strict_types = 1);

namespace PHPStan\Type\Accessory;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\ConstantReflection;
use PHPStan\Reflection\Dummy\DummyMethodReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\TrivialParametersAcceptor;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ArrayType;
use PHPStan\Type\CompoundType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use PHPStan\Type\Traits\TruthyBooleanTypeTrait;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;

class HasMethodType implements CompoundType, AccessoryType
{

	use TruthyBooleanTypeTrait;

	/** @var string */
	private $methodName;

	public function __construct(string $methodName)
	{
		$this->methodName = $methodName;
	}

	private function getCanonicalMethodName(): string
	{
		return strtolower($this->methodName);
	}

	public function getReferencedClasses(): array
	{
		return [];
	}

	public function accepts(Type $type, bool $strictTypes): TrinaryLogic
	{
		return TrinaryLogic::createFromBoolean($this->equals($type));
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		if ($type instanceof self) {
			return $this->equals($type)
				? TrinaryLogic::createYes()
				: TrinaryLogic::createMaybe();
		}

		if ($type instanceof CompoundType) {
			return $type->isSubTypeOf($this);
		}

		if (!(new ObjectWithoutClassType())->isSuperTypeOf($type)->yes()) {
			return TrinaryLogic::createNo();
		}

		if ($type->hasMethod($this->methodName)) {
			return TrinaryLogic::createYes();
		}

		if ($type instanceof TypeWithClassName) {
			$broker = Broker::getInstance();
			if ($broker->hasClass($type->getClassName())) {
				$classReflection = $broker->getClass($type->getClassName());
				if ($classReflection->isFinal()) {
					return TrinaryLogic::createNo();
				}
			}
		}

		return TrinaryLogic::createMaybe();
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

		if (!(new ObjectWithoutClassType())->isSuperTypeOf($otherType)->yes()) {
			return TrinaryLogic::createNo();
		}

		if ($otherType instanceof TypeWithClassName) {
			$broker = Broker::getInstance();
			if ($broker->hasClass($otherType->getClassName())) {
				$classReflection = $broker->getClass($otherType->getClassName());
				if ($classReflection->isFinal()) {
					return TrinaryLogic::createNo();
				}
			}
		}

		return TrinaryLogic::createMaybe();
	}

	public function equals(Type $type): bool
	{
		return $type instanceof self
			&& $this->getCanonicalMethodName() === $type->getCanonicalMethodName();
	}

	public function describe(\PHPStan\Type\VerbosityLevel $level): string
	{
		return sprintf('hasMethod(%s)', $this->methodName);
	}

	public function canAccessProperties(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function hasProperty(string $propertyName): bool
	{
		return false;
	}

	public function getProperty(string $propertyName, ClassMemberAccessAnswerer $scope): PropertyReflection
	{
		throw new \PHPStan\ShouldNotHappenException();
	}

	public function canCallMethods(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function hasMethod(string $methodName): bool
	{
		return $this->getCanonicalMethodName() === strtolower($methodName);
	}

	public function getMethod(string $methodName, ClassMemberAccessAnswerer $scope): MethodReflection
	{
		return new DummyMethodReflection($this->methodName);
	}

	public function canAccessConstants(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function hasConstant(string $constantName): bool
	{
		return false;
	}

	public function getConstant(string $constantName): ConstantReflection
	{
		throw new \PHPStan\ShouldNotHappenException();
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
		return TrinaryLogic::createFromBoolean(
			$this->getCanonicalMethodName() === '__invoke'
		);
	}

	public function getCallableParametersAcceptors(ClassMemberAccessAnswerer $scope): array
	{
		return [
			new TrivialParametersAcceptor(),
		];
	}

	public function isCloneable(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
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
		if ($this->getCanonicalMethodName() === '__tostring') {
			return new StringType();
		}

		return new ErrorType();
	}

	public function toArray(): Type
	{
		return new ArrayType(new MixedType(), new MixedType());
	}

	public static function __set_state(array $properties): Type
	{
		return new self($properties['methodName']);
	}

}
