<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Broker\Broker;
use PHPStan\TrinaryLogic;

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

	public function accepts(Type $type): bool
	{
		return (new ObjectType($this->baseClass))->accepts($type);
	}

	public function isSupersetOf(Type $type): TrinaryLogic
	{
		if ($type instanceof self) {
			return (new ObjectType($this->baseClass))->isSupersetOf($type);
		}

		if ($type instanceof ObjectType) {
			return TrinaryLogic::createMaybe()->and((new ObjectType($this->baseClass))->isSupersetOf($type));
		}

		if ($type instanceof CompoundType) {
			return $type->isSubsetOf($this);
		}

		return TrinaryLogic::createNo();
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

	public function isIterable(): TrinaryLogic
	{
		$broker = Broker::getInstance();

		if (!$broker->hasClass($this->baseClass)) {
			return TrinaryLogic::createMaybe();
		}

		if ($broker->getClass($this->baseClass)->isSubclassOf(\Traversable::class) || $this->baseClass === \Traversable::class) {
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

		if (!$broker->hasClass($this->baseClass)) {
			return new ErrorType();
		}

		$classReflection = $broker->getClass($this->baseClass);

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

		if (!$broker->hasClass($this->baseClass)) {
			return TrinaryLogic::createMaybe();
		}

		if ($broker->getClass($this->baseClass)->hasMethod('__invoke')) {
			return TrinaryLogic::createYes();
		}

		return TrinaryLogic::createNo();
	}

	public static function __set_state(array $properties): Type
	{
		return new self($properties['baseClass']);
	}

}
