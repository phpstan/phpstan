<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Broker\Broker;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Traits\MaybeCallableTypeTrait;
use PHPStan\Type\Traits\NonIterableTypeTrait;
use PHPStan\Type\Traits\NonObjectTypeTrait;
use PHPStan\Type\Traits\UndecidedBooleanTypeTrait;

class StringType implements Type
{

	use JustNullableTypeTrait;
	use MaybeCallableTypeTrait;
	use NonIterableTypeTrait;
	use NonObjectTypeTrait;
	use UndecidedBooleanTypeTrait;

	public function describe(): string
	{
		return 'string';
	}

	public function isOffsetAccessible(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function getOffsetValueType(Type $offsetType): Type
	{
		return new StringType();
	}

	public function setOffsetValueType(?Type $offsetType, Type $valueType): Type
	{
		return $this;
	}

	public function accepts(Type $type): bool
	{
		if ($type instanceof static) {
			return true;
		}

		if ($type instanceof CompoundType) {
			return CompoundTypeHelper::accepts($type, $this);
		}

		if ($type instanceof TypeWithClassName) {
			$broker = Broker::getInstance();
			if (!$broker->hasClass($type->getClassName())) {
				return false;
			}

			$typeClass = $broker->getClass($type->getClassName());
			return $typeClass->hasNativeMethod('__toString');
		}

		return false;
	}

	public static function __set_state(array $properties): Type
	{
		return new self();
	}

}
