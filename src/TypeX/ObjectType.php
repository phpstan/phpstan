<?php declare(strict_types = 1);

namespace PHPStan\TypeX;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassReflection;

class ObjectType extends BaseTypeX
{

	/** @var Broker */
	private $broker;

	/** @var null|string */
	protected $className;

	public function __construct(TypeXFactory $factory, Broker $broker, string $className = null)
	{
		parent::__construct($factory);
		$this->broker = $broker;
		$this->className = $className;
	}

	/**
	 * @return null|string
	 */
	public function getClassName()
	{
		return $this->className;
	}

	/**
	 * @return null|ClassReflection
	 */
	public function getClassRef()
	{
		if ($this->className === null || !$this->broker->hasClass($this->className)) {
			return null;
		}

		return $this->broker->getClass($this->className);
	}

	public function describe(): string
	{
		return $this->className === null ? 'object' : $this->className;
	}

	public function acceptsX(TypeX $otherType): bool
	{
		if ($this->className === null) {
			return $otherType instanceof self;
		}

		if (!($otherType instanceof self) || $otherType->className === null) {
			return false;
		}

		if ($this->className === $otherType->className) {
			return true; // speedup
		}

		if (!$this->broker->hasClass($otherType->className) || !$this->broker->hasClass($this->className)) {
			return false;
		}

		return $this->broker->getClass($otherType->className)->isSubclassOf($this->className);
	}

	public function isAssignable(): int
	{
		return self::RESULT_YES;
	}

	public function isCallable(): int
	{
		if ($this->className === null) {
			return self::RESULT_MAYBE;
		}

		if ($this->broker->hasClass($this->className)) {
			if ($this->broker->getClass($this->className)->hasMethod('__invoke')) {
				return self::RESULT_YES;
			}
		}

		return self::RESULT_NO;
	}

	public function getCallReturnType(TypeX ...$callArgsTypes): TypeX
	{
		return $this->factory->createErrorType();
	}

	public function isIterable(): int
	{
		if ($this->className === null) {
			return self::RESULT_MAYBE;
		}

		if ($this->broker->hasClass($this->className)) {
			if ($this->broker->getClass($this->className)->isSubclassOf(\Traversable::class)) {
				return self::RESULT_YES;
			}

			// This is a bit hard choice, but is technically correct because the actual value
			// may be instance of subclass which may implement Traversable.
			// The practical reason for this is that we need (do we really?) avoid reduction
			// iterable & Foo to void.
			// The best would probably be to iterate over all known subclasses and return MAYBE
			// if at least one of them implement Traversable
			return self::RESULT_MAYBE;
		}

		return self::RESULT_NO;
	}

	public function getIterableKeyType(): TypeX
	{
		if ($this->className === null) {
			return $this->factory->createMixedType();
		}

		if (!$this->broker->hasClass($this->className)) {
			return $this->factory->createErrorType(ErrorType::ITERATION_NOT_SUPPORTED);
		}

		$classRef = $this->broker->getClass($this->className);

		if ($classRef->isSubclassOf(\Iterator::class) && $classRef->hasMethod('key')) {
			$returnType = $classRef->getMethod('key')->getReturnType();
			return $this->factory->createFromLegacy($returnType);
		}

		if ($classRef->isSubclassOf(\IteratorAggregate::class) && $classRef->hasMethod('getIterator')) {
			$returnType = $classRef->getMethod('getIterator')->getReturnType();
			return $this->factory->createFromLegacy($returnType)->getIterableKeyType();
		}

		if ($classRef->isSubclassOf(\Traversable::class)) {
			return $this->factory->createMixedType();
		}

		// see comment in isIterable()
		// return $this->factory->createMixedType();
		return $this->factory->createErrorType(ErrorType::ITERATION_NOT_SUPPORTED);
	}

	public function getIterableValueType(): TypeX
	{
		if ($this->className === null) {
			return $this->factory->createMixedType();
		}

		if (!$this->broker->hasClass($this->className)) {
			return $this->factory->createErrorType(ErrorType::ITERATION_NOT_SUPPORTED);
		}

		$classRef = $this->broker->getClass($this->className);

		if ($classRef->isSubclassOf(\Iterator::class) && $classRef->hasMethod('current')) {
			$returnType = $classRef->getMethod('current')->getReturnType();
			return $this->factory->createFromLegacy($returnType);
		}

		if ($classRef->isSubclassOf(\IteratorAggregate::class) && $classRef->hasMethod('getIterator')) {
			$returnType = $classRef->getMethod('getIterator')->getReturnType();
			return $this->factory->createFromLegacy($returnType)->getIterableValueType();
		}

		if ($classRef->isSubclassOf(\Traversable::class)) {
			return $this->factory->createMixedType();
		}

		// see comment in isIterable()
		// return $this->factory->createMixedType();
		return $this->factory->createErrorType(ErrorType::ITERATION_NOT_SUPPORTED);
	}

	public function canCallMethodsX(): int
	{
		return self::RESULT_YES;
	}

	public function canAccessPropertiesX(): int
	{
		return self::RESULT_YES;
	}

	public function canAccessOffset(): int
	{
		if ($this->className === null) {
			return self::RESULT_MAYBE;
		}

		if ($this->broker->hasClass($this->className)) {
			if ($this->broker->getClass($this->className)->isSubclassOf(\ArrayAccess::class)) {
				return self::RESULT_YES;
			}
		}

		return self::RESULT_NO;
	}

	public function getOffsetValueType(TypeX $offsetType): TypeX
	{
		if ($this->className === null) {
			return $this->factory->createMixedType();
		}

		if ($this->broker->hasClass($this->className)) {
			$classRef = $this->broker->getClass($this->className);
			if ($classRef->isSubclassOf(\ArrayAccess::class) && $classRef->hasMethod('offsetGet')) {
				return $this->factory->createFromLegacy($classRef->getMethod('offsetGet')->getReturnType());
			}
		}

		return $this->factory->createErrorType();
	}
}
