<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassConstantReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;

class StaticType implements StaticResolvableType, TypeWithClassName
{

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

	public function accepts(Type $type): bool
	{
		return $this->staticObjectType->accepts($type);
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

	public function describe(): string
	{
		return sprintf('static(%s)', $this->baseClass);
	}

	public function canAccessProperties(): bool
	{
		return $this->staticObjectType->canAccessProperties();
	}

	public function hasProperty(string $propertyName): bool
	{
		return $this->staticObjectType->hasProperty($propertyName);
	}

	public function getProperty(string $propertyName, Scope $scope): PropertyReflection
	{
		return $this->staticObjectType->getProperty($propertyName, $scope);
	}

	public function canCallMethods(): bool
	{
		return $this->staticObjectType->canCallMethods();
	}

	public function hasMethod(string $methodName): bool
	{
		return $this->staticObjectType->hasMethod($methodName);
	}

	public function getMethod(string $methodName, Scope $scope): MethodReflection
	{
		return $this->staticObjectType->getMethod($methodName, $scope);
	}

	public function canAccessConstants(): bool
	{
		return $this->staticObjectType->canAccessConstants();
	}

	public function hasConstant(string $constantName): bool
	{
		return $this->staticObjectType->hasConstant($constantName);
	}

	public function getConstant(string $constantName): ClassConstantReflection
	{
		return $this->staticObjectType->getConstant($constantName);
	}

	public function isDocumentableNatively(): bool
	{
		return $this->staticObjectType->isDocumentableNatively();
	}

	public function resolveStatic(string $className): Type
	{
		return new ObjectType($className);
	}

	public function changeBaseClass(string $className): StaticResolvableType
	{
		$thisClass = get_class($this);
		return new $thisClass($className);
	}

	public function isIterable(): TrinaryLogic
	{
		$broker = Broker::getInstance();

		if (!$broker->hasClass($this->baseClass)) {
			return TrinaryLogic::createMaybe();
		}

		$classReflection = $broker->getClass($this->baseClass);
		if ($classReflection->isSubclassOf(\Traversable::class) || $classReflection->getName() === \Traversable::class) {
			return TrinaryLogic::createYes();
		}

		return TrinaryLogic::createNo();
	}

	public function getIterableKeyType(): Type
	{
		$broker = Broker::getInstance();

		if (!$broker->hasClass($this->baseClass)) {
			return new ErrorType();
		}

		$classReflection = $broker->getClass($this->baseClass);

		if ($classReflection->isSubclassOf(\Iterator::class) && $classReflection->hasNativeMethod('key')) {
			return $classReflection->getNativeMethod('key')->getReturnType();
		}

		if ($classReflection->isSubclassOf(\IteratorAggregate::class) && $classReflection->hasNativeMethod('getIterator')) {
			return RecursionGuard::run($this, function () use ($classReflection) {
				return $classReflection->getNativeMethod('getIterator')->getReturnType()->getIterableKeyType();
			});
		}

		if ($classReflection->isSubclassOf(\Traversable::class)) {
			return new MixedType();
		}

		return new ErrorType();
	}

	public function getIterableValueType(): Type
	{
		$broker = Broker::getInstance();

		if (!$broker->hasClass($this->baseClass)) {
			return new ErrorType();
		}

		$classReflection = $broker->getClass($this->baseClass);

		if ($classReflection->isSubclassOf(\Iterator::class) && $classReflection->hasNativeMethod('current')) {
			return $classReflection->getNativeMethod('current')->getReturnType();
		}

		if ($classReflection->isSubclassOf(\IteratorAggregate::class) && $classReflection->hasNativeMethod('getIterator')) {
			return RecursionGuard::run($this, function () use ($classReflection) {
				return $classReflection->getNativeMethod('getIterator')->getReturnType()->getIterableValueType();
			});
		}

		if ($classReflection->isSubclassOf(\Traversable::class)) {
			return new MixedType();
		}

		return new ErrorType();
	}

	public function isCallable(): TrinaryLogic
	{
		$broker = Broker::getInstance();

		if (!$broker->hasClass($this->baseClass)) {
			return TrinaryLogic::createMaybe();
		}

		if ($broker->getClass($this->baseClass)->hasMethod('__invoke')) {
			return TrinaryLogic::createYes();
		}

		return TrinaryLogic::createNo();
	}

	public function isClonable(): bool
	{
		return true;
	}

	public static function __set_state(array $properties): Type
	{
		return new static($properties['baseClass']);
	}

}
