<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Broker\Broker;

class StaticType implements StaticResolvableType
{

	/** @var string */
	private $baseClass;

	public function __construct(string $baseClass)
	{
		$this->baseClass = $baseClass;
	}

	public function getClass(): string
	{
		return $this->baseClass;
	}

	/**
	 * @return string[]
	 */
	public function getReferencedClasses(): array
	{
		return [$this->getClass()];
	}

	public function getBaseClass(): string
	{
		return $this->baseClass;
	}

	public function combineWith(Type $otherType): Type
	{
		return new self($this->baseClass);
	}

	public function accepts(Type $type): bool
	{
		return (new ObjectType($this->baseClass))->accepts($type);
	}

	public function describe(): string
	{
		return sprintf('static(%s)', $this->baseClass);
	}

	public function canAccessProperties(): bool
	{
		return true;
	}

	public function canCallMethods(): bool
	{
		return true;
	}

	public function isDocumentableNatively(): bool
	{
		return true;
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

	public function isIterable(): int
	{
		$broker = Broker::getInstance();

		if ($broker->hasClass($this->baseClass)) {
			if ($broker->getClass($this->baseClass)->isSubclassOf(\Traversable::class)) {
				return TrinaryLogic::YES;
			}
		}

		return TrinaryLogic::NO;
	}

	public function getIterableKeyType(): Type
	{
		$broker = Broker::getInstance();

		if (!$broker->hasClass($this->baseClass)) {
			return new ErrorType();
		}

		$classRef = $broker->getClass($this->baseClass);

		if ($classRef->isSubclassOf(\Iterator::class) && $classRef->hasMethod('key')) {
			return $classRef->getMethod('key')->getReturnType();
		}

		if ($classRef->isSubclassOf(\IteratorAggregate::class) && $classRef->hasMethod('getIterator')) {
			return $classRef->getMethod('getIterator')->getReturnType()->getIterableKeyType();
		}

		if ($classRef->isSubclassOf(\Traversable::class)) {
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

		$classRef = $broker->getClass($this->baseClass);

		if ($classRef->isSubclassOf(\Iterator::class) && $classRef->hasMethod('current')) {
			return $classRef->getMethod('current')->getReturnType();
		}

		if ($classRef->isSubclassOf(\IteratorAggregate::class) && $classRef->hasMethod('getIterator')) {
			return $classRef->getMethod('getIterator')->getReturnType()->getIterableValueType();
		}

		if ($classRef->isSubclassOf(\Traversable::class)) {
			return new MixedType();
		}

		return new ErrorType();
	}

	public static function __set_state(array $properties): Type
	{
		return new self($properties['baseClass']);
	}

}
