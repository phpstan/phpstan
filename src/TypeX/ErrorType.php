<?php declare(strict_types = 1);

namespace PHPStan\TypeX;

class ErrorType extends BaseTypeX
{

	public function describe(): string
	{
		return '*ERROR*';
	}

	public function acceptsX(TypeX $otherType): bool
	{
		return FALSE;
	}

	public function isAssignable(): int
	{
		return self::RESULT_NO;
	}

	public function isCallable(): int
	{
		return self::RESULT_NO;
	}

	public function getCallReturnType(TypeX ...$callArgsTypes): TypeX
	{
		return $this;
	}

	public function isIterable(): int
	{
		return self::RESULT_NO;
	}

	public function getIterableKeyType(): TypeX
	{
		return $this;
	}

	public function getIterableValueType(): TypeX
	{
		return $this;
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
		return self::RESULT_NO;
	}
}
