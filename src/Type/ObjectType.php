<?php declare(strict_types = 1);

namespace PHPStan\Type;

class ObjectType implements Type
{

	/** @var string */
	private $class;

	/** @var bool */
	private $nullable;

	public function __construct(string $class, bool $nullable)
	{
		$this->class = $class;
		$this->nullable = $nullable;
	}

	public function getClass(): string
	{
		return $this->class;
	}

	public function isNullable(): bool
	{
		return $this->nullable;
	}

	public function combineWith(Type $otherType): Type
	{
		if ($otherType instanceof self && $this->getClass() == $otherType->getClass()) {
			return new self($this->getClass(), $this->isNullable() || $otherType->isNullable());
		}

		if ($otherType instanceof NullType) {
			return $this->makeNullable();
		}

		return new MixedType($this->isNullable() || $otherType->isNullable());
	}

	public function makeNullable(): Type
	{
		return new self($this->getClass(), true);
	}

	public function accepts(Type $type): bool
	{
		if ($type instanceof MixedType) {
			return true;
		}

		if ($this->isNullable() && $type instanceof NullType) {
			return true;
		}

		if ($type->getClass() === null) {
			return false;
		}

		if ($this->getClass() === $type->getClass()) {
			return true;
		}

		if (!$this->exists($this->getClass()) || !$this->exists($type->getClass())) {
			return false;
		}

		$thisReflection = new \ReflectionClass($this->getClass());
		$thatReflection = new \ReflectionClass($type->getClass());

		if ($thisReflection->isInterface() && $thatReflection->isInterface()) {
			return $thatReflection->implementsInterface($this->getClass());
		}

		return $thatReflection->isSubclassOf($this->getClass());
	}

	private function exists(string $className): bool
	{
		try {
			return class_exists($className) || interface_exists($className) || trait_exists($className);
		} catch (\Throwable $t) {
			throw new \PHPStan\Broker\ClassAutoloadingException(
				$className,
				$t
			);
		}
	}

	public function describe(): string
	{
		return $this->class;
	}

}
