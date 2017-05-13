<?php declare(strict_types = 1);

namespace PHPStan\TypeX;

use PHPStan\Type\Type;

class MixedType extends BaseTypeX // TODO: does MixedType include VoidType?
{
	public function describe(): string
	{
		return 'mixed';
	}

	public function acceptsX(TypeX $otherType): bool
	{
		return TRUE;
	}

	public function isAssignable(): int
	{
		return self::RESULT_YES; // TODO: or MAYBE?
	}

	public function isCallable(): int
	{
		return self::RESULT_MAYBE;
	}

	public function getCallReturnType(TypeX ...$callArgsTypes): TypeX
	{
		return $this->factory->createMixedType();
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
