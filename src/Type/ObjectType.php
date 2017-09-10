<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;

class ObjectType implements Type
{

	/** @var string */
	private $class;

	public function __construct(string $class)
	{
		$this->class = $class;
	}

	public function getClass(): string
	{
		return $this->class;
	}

	public function hasProperty(string $propertyName): bool
	{
		$broker = Broker::getInstance();
		if (!$broker->hasClass($this->class)) {
			return false;
		}

		return $broker->getClass($this->class)->hasProperty($propertyName);
	}

	public function getProperty(string $propertyName, Scope $scope): PropertyReflection
	{
		$broker = Broker::getInstance();
		return $broker->getClass($this->class)->getProperty($propertyName, $scope);
	}

	/**
	 * @return string[]
	 */
	public function getReferencedClasses(): array
	{
		return [$this->getClass()];
	}

	public function accepts(Type $type): bool
	{
		if ($type instanceof StaticType) {
			return $this->checkSubclassAcceptability($type->getBaseClass());
		}

		if ($type instanceof CompoundType) {
			return CompoundTypeHelper::accepts($type, $this);
		}

		if ($type->getClass() === null) {
			return false;
		}

		return $this->checkSubclassAcceptability($type->getClass());
	}

	public function isSupersetOf(Type $type): TrinaryLogic
	{
		if ($type instanceof CompoundType) {
			return $type->isSubsetOf($this);
		}

		$thisClassName = $this->class;
		$thatClassName = $type->getClass();

		if ($thatClassName === null) {
			return TrinaryLogic::createNo();
		}

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
		if ($this->getClass() === $thatClass) {
			return true;
		}

		$broker = Broker::getInstance();

		if (!$broker->hasClass($this->getClass()) || !$broker->hasClass($thatClass)) {
			return false;
		}

		$thisReflection = $broker->getClass($this->getClass());
		$thatReflection = $broker->getClass($thatClass);

		if ($thisReflection->getName() === $thatReflection->getName()) {
			// class alias
			return true;
		}

		if ($thisReflection->isInterface() && $thatReflection->isInterface()) {
			return $thatReflection->getNativeReflection()->implementsInterface($this->getClass());
		}

		return $thatReflection->isSubclassOf($this->getClass());
	}

	public function describe(): string
	{
		return $this->class;
	}

	public function canAccessProperties(): bool
	{
		return true;
	}

	public function canCallMethods(): bool
	{
		return strtolower($this->class) !== 'stdclass';
	}

	public function isDocumentableNatively(): bool
	{
		return true;
	}

	public function isIterable(): TrinaryLogic
	{
		$broker = Broker::getInstance();

		if (!$broker->hasClass($this->class)) {
			return TrinaryLogic::createMaybe();
		}

		if ($broker->getClass($this->class)->isSubclassOf(\Traversable::class) || $this->class === \Traversable::class) {
			return TrinaryLogic::createYes();
		}

		return TrinaryLogic::createNo();
	}

	public function getIterableKeyType(): Type
	{
		$broker = Broker::getInstance();

		if (!$broker->hasClass($this->class)) {
			return new ErrorType();
		}

		$classReflection = $broker->getClass($this->class);

		if ($classReflection->isSubclassOf(\Iterator::class) && $classReflection->hasMethod('key')) {
			return $classReflection->getMethod('key')->getReturnType();
		}

		if ($classReflection->isSubclassOf(\IteratorAggregate::class) && $classReflection->hasMethod('getIterator')) {
			return RecursionGuard::run($this, function () use ($classReflection) {
				return $classReflection->getMethod('getIterator')->getReturnType()->getIterableKeyType();
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

		if (!$broker->hasClass($this->class)) {
			return new ErrorType();
		}

		$classReflection = $broker->getClass($this->class);

		if ($classReflection->isSubclassOf(\Iterator::class) && $classReflection->hasMethod('current')) {
			return $classReflection->getMethod('current')->getReturnType();
		}

		if ($classReflection->isSubclassOf(\IteratorAggregate::class) && $classReflection->hasMethod('getIterator')) {
			return RecursionGuard::run($this, function () use ($classReflection) {
				return $classReflection->getMethod('getIterator')->getReturnType()->getIterableValueType();
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

		if (!$broker->hasClass($this->class)) {
			return TrinaryLogic::createMaybe();
		}

		if ($broker->getClass($this->class)->hasMethod('__invoke')) {
			return TrinaryLogic::createYes();
		}

		return TrinaryLogic::createNo();
	}

	public static function __set_state(array $properties): Type
	{
		return new self($properties['class']);
	}

}
