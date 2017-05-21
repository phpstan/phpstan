<?php declare(strict_types = 1);

namespace PHPStan\Type;

class ObjectType implements Type
{

	use ClassTypeHelperTrait;

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
		if ($type instanceof MixedType) {
			return true;
		}

		if ($type instanceof StaticType) {
			return $this->checkSubclassAcceptability($type->getBaseClass());
		}

		if ($type instanceof UnionType) {
			return UnionTypeHelper::acceptsAll($this, $type);
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

		if ($thisReflection->getName() === $thatReflection->getName()) {
			// class alias
			return true;
		}

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
		return strtolower($this->class) !== 'stdclass';
	}

	public function isDocumentableNatively(): bool
	{
		return true;
	}

	public function isIterable(): int
	{
		return self::RESULT_NO;
	}

	public function getIterableKeyType(): Type
	{
		return new MixedType();
	}

	public function getIterableValueType(): Type
	{
		return new MixedType();
	}

}
