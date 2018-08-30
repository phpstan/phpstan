<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\ConstantReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Traits\TruthyBooleanTypeTrait;

class StaticType implements StaticResolvableType, TypeWithClassName
{

	use TruthyBooleanTypeTrait;

	/** @var string */
	private $baseClass;

	/** @var \PHPStan\Type\ObjectType */
	private $staticObjectType;

	public function __construct(string $baseClass)
	{
		$this->baseClass = $baseClass;
		$this->staticObjectType = new ObjectType($baseClass);
	}

	public function getClassName(): string
	{
		return $this->baseClass;
	}

	protected function getStaticObjectType(): ObjectType
	{
		return $this->staticObjectType;
	}

	/**
	 * @return string[]
	 */
	public function getReferencedClasses(): array
	{
		return $this->staticObjectType->getReferencedClasses();
	}

	public function getBaseClass(): string
	{
		return $this->baseClass;
	}

	public function accepts(Type $type, bool $strictTypes): TrinaryLogic
	{
		return $this->staticObjectType->accepts($type, $strictTypes);
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		if ($type instanceof self) {
			return $this->staticObjectType->isSuperTypeOf($type);
		}

		if ($type instanceof ObjectType) {
			return TrinaryLogic::createMaybe()->and($this->staticObjectType->isSuperTypeOf($type));
		}

		if ($type instanceof CompoundType) {
			return $type->isSubTypeOf($this);
		}

		return TrinaryLogic::createNo();
	}

	public function equals(Type $type): bool
	{
		return $this->staticObjectType->equals($type);
	}

	public function describe(VerbosityLevel $level): string
	{
		return sprintf('static(%s)', $this->staticObjectType->describe($level));
	}

	public function canAccessProperties(): TrinaryLogic
	{
		return $this->staticObjectType->canAccessProperties();
	}

	public function hasProperty(string $propertyName): bool
	{
		return $this->staticObjectType->hasProperty($propertyName);
	}

	public function getProperty(string $propertyName, ClassMemberAccessAnswerer $scope): PropertyReflection
	{
		return $this->staticObjectType->getProperty($propertyName, $scope);
	}

	public function canCallMethods(): TrinaryLogic
	{
		return $this->staticObjectType->canCallMethods();
	}

	public function hasMethod(string $methodName): bool
	{
		return $this->staticObjectType->hasMethod($methodName);
	}

	public function getMethod(string $methodName, ClassMemberAccessAnswerer $scope): MethodReflection
	{
		return $this->staticObjectType->getMethod($methodName, $scope);
	}

	public function canAccessConstants(): TrinaryLogic
	{
		return $this->staticObjectType->canAccessConstants();
	}

	public function hasConstant(string $constantName): bool
	{
		return $this->staticObjectType->hasConstant($constantName);
	}

	public function getConstant(string $constantName): ConstantReflection
	{
		return $this->staticObjectType->getConstant($constantName);
	}

	public function resolveStatic(string $className): Type
	{
		return new ObjectType($className);
	}

	public function changeBaseClass(string $className): StaticResolvableType
	{
		$thisClass = static::class;
		return new $thisClass($className);
	}

	public function isIterable(): TrinaryLogic
	{
		return $this->staticObjectType->isInstanceOf(\Traversable::class);
	}

	public function getIterableKeyType(): Type
	{
		return $this->staticObjectType->getIterableKeyType();
	}

	public function getIterableValueType(): Type
	{
		return $this->staticObjectType->getIterableValueType();
	}

	public function isOffsetAccessible(): TrinaryLogic
	{
		return $this->staticObjectType->isInstanceOf(\ArrayAccess::class);
	}

	public function hasOffsetValueType(Type $offsetType): TrinaryLogic
	{
		return $this->staticObjectType->hasOffsetValueType($offsetType);
	}

	public function getOffsetValueType(Type $offsetType): Type
	{
		return $this->staticObjectType->getOffsetValueType($offsetType);
	}

	public function setOffsetValueType(?Type $offsetType, Type $valueType): Type
	{
		return $this->staticObjectType->setOffsetValueType($offsetType, $valueType);
	}

	public function isCallable(): TrinaryLogic
	{
		return $this->staticObjectType->isCallable();
	}

	/**
	 * @param \PHPStan\Reflection\ClassMemberAccessAnswerer $scope
	 * @return \PHPStan\Reflection\ParametersAcceptor[]
	 */
	public function getCallableParametersAcceptors(ClassMemberAccessAnswerer $scope): array
	{
		return $this->staticObjectType->getCallableParametersAcceptors($scope);
	}

	public function isCloneable(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function toNumber(): Type
	{
		return new ErrorType();
	}

	public function toString(): Type
	{
		return $this->staticObjectType->toString();
	}

	public function toInteger(): Type
	{
		return new ErrorType();
	}

	public function toFloat(): Type
	{
		return new ErrorType();
	}

	public function toArray(): Type
	{
		return $this->staticObjectType->toArray();
	}

	/**
	 * @param mixed[] $properties
	 * @return Type
	 */
	public static function __set_state(array $properties): Type
	{
		return new static($properties['baseClass']);
	}

}
