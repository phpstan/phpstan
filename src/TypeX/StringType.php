<?php declare(strict_types = 1);

namespace PHPStan\TypeX;

class StringType extends BaseTypeX
{
	public function describe(): string
	{
		return 'string';
	}

	public function acceptsX(TypeX $otherType): bool
	{
		return $otherType instanceof self;
	}

	public function isAssignable(): int
	{
		return self::RESULT_YES;
	}

	public function isCallable(): int
	{
		return self::RESULT_MAYBE;
	}

	public function getCallReturnType(TypeX ...$callArgsTypes): TypeX
	{
		return $this->factory->createErrorType();
	}

	public function isIterable(): int
	{
		return self::RESULT_NO;
	}

	public function getIterableKeyType(): TypeX
	{
		return $this->factory->createErrorType();
	}

	public function getIterableValueType(): TypeX
	{
		return $this->factory->createErrorType();
	}

	public function canCallMethodsX(): int
	{
		return self::RESULT_NO;
	}

	public function canAccessPropertiesX(): int
	{
		return self::RESULT_NO;
	}

	public function canAccessOffset(): int
	{
		return self::RESULT_YES;
	}

	public function getOffsetValueType(TypeX $offsetType): TypeX
	{
		return $this->factory->createStringType();
	}

	public function setOffsetValueType(TypeX $offsetType = null, TypeX $valueType): TypeX
	{
		return $this->factory->createErrorType();
	}
}
