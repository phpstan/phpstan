<?php declare(strict_types = 1);

namespace PHPStan\TypeX;

class ComplementType extends BaseTypeX
{

	/** @var TypeX */
	private $innerType;

	public function __construct(TypeXFactory $factory, TypeX $innerType)
	{
		parent::__construct($factory);
		$this->innerType = $innerType;
	}

	public function getInnerType(): TypeX
	{
		return $this->innerType;
	}

	public function describe(): string
	{
		return '~' . $this->innerType->describe();
	}

	public function acceptsX(TypeX $otherType): bool
	{
		return !$this->innerType->acceptsX($otherType)
			&& !$otherType->acceptsX($this->innerType);
	}

	public function isAssignable(): int
	{
		return self::RESULT_MAYBE;
	}

	public function isCallable(): int
	{
		return self::RESULT_MAYBE;
	}

//	public function getCallReturnType(TypeX ...$callArgsTypes): TypeX
//	{
//		return $this->resultType;
//	}

	public function isIterable(): int
	{
		return self::RESULT_MAYBE;
	}

	public function getIterableKeyType(): TypeX
	{
		return $this->factory->createMixedType();
	}

	public function getIterableValueType(): TypeX
	{
		return $this->factory->createMixedType();
	}

	public function canCallMethodsX(): int
	{
		return self::RESULT_MAYBE;
	}

	public function canAccessPropertiesX(): int
	{
		return self::RESULT_MAYBE;
	}

	public function canAccessOffset(): int
	{
		return self::RESULT_MAYBE;
	}
}
