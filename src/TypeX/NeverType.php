<?php declare(strict_types = 1);

namespace PHPStan\TypeX;

/**
 * Similar to VoidType but NeverType can not even be returned.
 * It is the return type of methods which always throw exception.
 */
class NeverType extends BaseTypeX
{
	public function describe(): string
	{
		return '*NEVER*';
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
		return self::RESULT_NO;
	}
}
