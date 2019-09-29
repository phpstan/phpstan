<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Broker\Broker;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Constant\ConstantStringType;

class ClassStringType extends StringType
{

	public function describe(VerbosityLevel $level): string
	{
		return 'class-string';
	}

	public function accepts(Type $type, bool $strictTypes): TrinaryLogic
	{
		if ($type instanceof CompoundType) {
			return $type->isAcceptedBy($this, $strictTypes);
		}

		if ($type instanceof ConstantStringType) {
			$broker = Broker::getInstance();
			return TrinaryLogic::createFromBoolean($broker->hasClass($type->getValue()));
		}

		if ($type instanceof self) {
			return TrinaryLogic::createYes();
		}

		return TrinaryLogic::createNo();
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		if ($type instanceof ConstantStringType) {
			$broker = Broker::getInstance();
			return TrinaryLogic::createFromBoolean($broker->hasClass($type->getValue()));
		}

		if ($type instanceof self) {
			return TrinaryLogic::createYes();
		}

		if ($type instanceof parent) {
			return TrinaryLogic::createMaybe();
		}

		if ($type instanceof CompoundType) {
			return $type->isSubTypeOf($this);
		}

		return TrinaryLogic::createNo();
	}

	/**
	 * @param mixed[] $properties
	 * @return Type
	 */
	public static function __set_state(array $properties): Type
	{
		return new self();
	}

}
