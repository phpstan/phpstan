<?php declare(strict_types = 1);

namespace PHPStan\TypeX;

class CallableType extends BaseTypeX
{

	/** @var TypeX */
	private $resultType;

	public function __construct(TypeXFactory $factory, TypeX $resultType)
	{
		parent::__construct($factory);
		$this->resultType = $resultType;
	}

	public function describe(): string
	{
		return 'callable';
	}

	public function acceptsX(TypeX $otherType): bool
	{
		return $otherType->isCallable() === self::RESULT_YES;
	}

	public function isAssignable(): int
	{
		return self::RESULT_YES;
	}

	public function isCallable(): int
	{
		return self::RESULT_YES;
	}

	public function getCallReturnType(TypeX ...$callArgsTypes): TypeX
	{
		return $this->resultType;
	}

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
