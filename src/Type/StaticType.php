<?php declare(strict_types = 1);

namespace PHPStan\Type;

class StaticType implements Type
{

	/** @var string */
	private $baseClass;

	/** @var bool */
	private $nullable;

	public function __construct(string $baseClass, bool $nullable)
	{
		$this->baseClass = $baseClass;
		$this->nullable = $nullable;
	}

	/**
	 * @return string|null
	 */
	public function getClass()
	{
		return null;
	}

	public function getBaseClass(): string
	{
		return $this->baseClass;
	}

	public function isNullable(): bool
	{
		return $this->nullable;
	}

	public function combineWith(Type $otherType): Type
	{
		return new self($this->baseClass, $this->isNullable() || $otherType->isNullable());
	}

	public function makeNullable(): Type
	{
		return new self($this->baseClass, true);
	}

	public function accepts(Type $type): bool
	{
		return (new ObjectType($this->baseClass, $this->isNullable()))->accepts($type);
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

}
