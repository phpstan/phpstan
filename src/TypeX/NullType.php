<?php declare(strict_types = 1);

namespace PHPStan\TypeX;

class NullType extends BaseTypeX
{
	public function describe(): string
	{
		return 'null';
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
		return $this->factory->createErrorType(ErrorType::ITERATION_NOT_SUPPORTED);
	}

	public function getIterableValueType(): TypeX
	{
		return $this->factory->createErrorType(ErrorType::ITERATION_NOT_SUPPORTED);
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
