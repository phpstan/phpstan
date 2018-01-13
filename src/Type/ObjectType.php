<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassConstantReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;

class ObjectType implements TypeWithClassName
{

	/** @var string */
	private $className;

	public function __construct(string $className)
	{
		$this->className = $className;
	}

	public function getClassName(): string
	{
		return $this->className;
	}

	public function hasProperty(string $propertyName): bool
	{
		$broker = Broker::getInstance();
		if (!$broker->hasClass($this->className)) {
			return false;
		}

		return $broker->getClass($this->className)->hasProperty($propertyName);
	}

	public function getProperty(string $propertyName, Scope $scope): PropertyReflection
	{
		$broker = Broker::getInstance();
		return $broker->getClass($this->className)->getProperty($propertyName, $scope);
	}

	/**
	 * @return string[]
	 */
	public function getReferencedClasses(): array
	{
		return [$this->className];
	}

	public function accepts(Type $type): bool
	{
		if ($type instanceof StaticType) {
			return $this->checkSubclassAcceptability($type->getBaseClass());
		}

		if ($type instanceof CompoundType) {
			return CompoundTypeHelper::accepts($type, $this);
		}

		if (!$type instanceof TypeWithClassName) {
			return false;
		}

		return $this->checkSubclassAcceptability($type->getClassName());
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		if ($type instanceof CompoundType) {
			return $type->isSubTypeOf($this);
		}

		if ($type instanceof ObjectWithoutClassType) {
			return TrinaryLogic::createMaybe();
		}

		if (!$type instanceof TypeWithClassName) {
			return TrinaryLogic::createNo();
		}

		$thisClassName = $this->className;
		$thatClassName = $type->getClassName();

		if ($thatClassName === $thisClassName) {
			return TrinaryLogic::createYes();
		}

		$broker = Broker::getInstance();

		if (!$broker->hasClass($thisClassName) || !$broker->hasClass($thatClassName)) {
			return TrinaryLogic::createMaybe();
		}

		$thisClassReflection = $broker->getClass($thisClassName);
		$thatClassReflection = $broker->getClass($thatClassName);

		if ($thisClassReflection->getName() === $thatClassReflection->getName()) {
			return TrinaryLogic::createYes();
		}

		if ($thatClassReflection->isSubclassOf($thisClassName)) {
			return TrinaryLogic::createYes();
		}

		if ($thisClassReflection->isSubclassOf($thatClassName)) {
			return TrinaryLogic::createMaybe();
		}

		if ($thisClassReflection->isInterface() && !$thatClassReflection->getNativeReflection()->isFinal()) {
			return TrinaryLogic::createMaybe();
		}

		if ($thatClassReflection->isInterface() && !$thisClassReflection->getNativeReflection()->isFinal()) {
			return TrinaryLogic::createMaybe();
		}

		return TrinaryLogic::createNo();
	}

	private function checkSubclassAcceptability(string $thatClass): bool
	{
		if ($this->className === $thatClass) {
			return true;
		}

		$broker = Broker::getInstance();

		if (!$broker->hasClass($this->className) || !$broker->hasClass($thatClass)) {
			return false;
		}

		$thisReflection = $broker->getClass($this->className);
		$thatReflection = $broker->getClass($thatClass);

		if ($thisReflection->getName() === $thatReflection->getName()) {
			// class alias
			return true;
		}

		if ($thisReflection->isInterface() && $thatReflection->isInterface()) {
			return $thatReflection->getNativeReflection()->implementsInterface($this->className);
		}

		return $thatReflection->isSubclassOf($this->className);
	}

	public function describe(): string
	{
		return $this->className;
	}

	public function canAccessProperties(): bool
	{
		return true;
	}

	public function canCallMethods(): bool
	{
		return strtolower($this->className) !== 'stdclass';
	}

	public function hasMethod(string $methodName): bool
	{
		$broker = Broker::getInstance();
		if (!$broker->hasClass($this->className)) {
			return false;
		}

		return $broker->getClass($this->className)->hasMethod($methodName);
	}

	public function getMethod(string $methodName, Scope $scope): MethodReflection
	{
		$broker = Broker::getInstance();
		return $broker->getClass($this->className)->getMethod($methodName, $scope);
	}

	public function canAccessConstants(): bool
	{
		return true;
	}

	public function hasConstant(string $constantName): bool
	{
		$broker = Broker::getInstance();
		if (!$broker->hasClass($this->className)) {
			return false;
		}

		return $broker->getClass($this->className)->hasConstant($constantName);
	}

	public function getConstant(string $constantName): ClassConstantReflection
	{
		$broker = Broker::getInstance();
		return $broker->getClass($this->className)->getConstant($constantName);
	}

	public function isDocumentableNatively(): bool
	{
		return true;
	}

	public function isIterable(): TrinaryLogic
	{
		$broker = Broker::getInstance();

		if (!$broker->hasClass($this->className)) {
			return TrinaryLogic::createMaybe();
		}

		$classReflection = $broker->getClass($this->className);
		if ($classReflection->isSubclassOf(\Traversable::class) || $classReflection->getName() === \Traversable::class) {
			return TrinaryLogic::createYes();
		}

		if ($classReflection->isInterface()) {
			return TrinaryLogic::createMaybe();
		}

		return TrinaryLogic::createNo();
	}

	public function getIterableKeyType(): Type
	{
		$broker = Broker::getInstance();

		if (!$broker->hasClass($this->className)) {
			return new ErrorType();
		}

		$classReflection = $broker->getClass($this->className);

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

		if (!$broker->hasClass($this->className)) {
			return new ErrorType();
		}

		$classReflection = $broker->getClass($this->className);

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

		if (!$broker->hasClass($this->className)) {
			return TrinaryLogic::createMaybe();
		}

		$classReflection = $broker->getClass($this->className);
		if ($classReflection->hasNativeMethod('__invoke')) {
			return TrinaryLogic::createYes();
		}

		if (!$classReflection->getNativeReflection()->isFinal()) {
			return TrinaryLogic::createMaybe();
		}

		return TrinaryLogic::createNo();
	}

	public function isClonable(): bool
	{
		return true;
	}

	public static function __set_state(array $properties): Type
	{
		return new self($properties['className']);
	}

}
