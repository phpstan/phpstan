<?php declare(strict_types = 1);

namespace PHPStan\Type;

class ObjectType implements Type
{

	use ClassTypeHelperTrait;

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

		if ($type instanceof StaticType) {
			return $this->checkSubclassAcceptability($type->getBaseClass());
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

		if (!$this->exists($this->getClass()) || !$this->exists($thatClass)) {
			return false;
		}

		$thisReflection = new \ReflectionClass($this->getClass());
		$thatReflection = new \ReflectionClass($thatClass);

		if ($thisReflection->isInterface() && $thatReflection->isInterface()) {
			return $thatReflection->implementsInterface($this->getClass());
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
		return $this->class !== 'stdClass';
	}

}
