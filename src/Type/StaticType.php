<?php declare(strict_types = 1);

namespace PHPStan\Type;

class StaticType implements StaticResolvableType
{

	/** @var string */
	private $baseClass;

	public function __construct(string $baseClass)
	{
		$this->baseClass = $baseClass;
	}

	/**
	 * @return string|null
	 */
	public function getClass()
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
