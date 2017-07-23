<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Broker\Broker;

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

	/**
	 * @return string[]
	 */
	public function getReferencedClasses(): array
	{
		return [$this->getClass()];
	}

	public function combineWith(Type $otherType): Type
	{
		if ($otherType instanceof self && $this->getClass() === $otherType->getClass()) {
			return new self($this->getClass());
		}

		return TypeCombinator::combine($this, $otherType);
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

	public function isIterable(): int
	{
		$broker = Broker::getInstance();

		if (!$broker->hasClass($this->class)) {
			return TrinaryLogic::MAYBE;
		}

		if ($broker->getClass($this->class)->isSubclassOf(\Traversable::class) || $this->class === \Traversable::class) {
			return TrinaryLogic::YES;
		}

		return TrinaryLogic::NO;
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

	public static function __set_state(array $properties): Type
	{
		return new self($properties['class']);
	}

}
