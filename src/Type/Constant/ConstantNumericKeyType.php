<?php declare(strict_types = 1);

namespace PHPStan\Type\Constant;

use PHPStan\TrinaryLogic;
use PHPStan\Type\CompoundType;
use PHPStan\Type\CompoundTypeHelper;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class ConstantNumericKeyType extends ConstantIntegerType implements CompoundType
{

	public function __construct(string $value)
	{
		if (preg_match('/^\d+$/', $value) !== 1) {
			throw new \PHPStan\ShouldNotHappenException(sprintf('Not a numeric key \'%s\'', $value));
		}

		parent::__construct((int) $value);
	}

	public function describe(VerbosityLevel $level): string
	{
		return $level->handle(
			static function (): string {
				return '(int&string)';
			},
			function (): string {
				return sprintf('%s', $this->getValue());
			}
		);
	}

	public function accepts(Type $type, bool $strictTypes): TrinaryLogic
	{
		if ($type instanceof ConstantIntegerType) {
			return TrinaryLogic::createFromBoolean($this->getValue() === $type->getValue());
		}

		if ($type instanceof ConstantStringType) {
			return TrinaryLogic::createFromBoolean((string) $this->getValue() === $type->getValue());
		}

		if ($type instanceof CompoundType) {
			return CompoundTypeHelper::accepts($type, $this, $strictTypes);
		}

		return TrinaryLogic::createNo();
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		if ($type instanceof ConstantIntegerType) {
			return TrinaryLogic::createFromBoolean($this->getValue() === $type->getValue());
		}

		if ($type instanceof ConstantStringType) {
			return TrinaryLogic::createFromBoolean((string) $this->getValue() === $type->getValue());
		}

		if ($type instanceof IntegerType || $type instanceof StringType) {
			return TrinaryLogic::createMaybe();
		}

		if ($type instanceof CompoundType) {
			return $type->isSubTypeOf($this);
		}

		return TrinaryLogic::createNo();
	}

	public function isSubTypeOf(Type $otherType): TrinaryLogic
	{
		return TrinaryLogic::createNo()->or(
			$otherType->isSuperTypeOf($this->toInteger()),
			$otherType->isSuperTypeOf($this->toString())
		);
	}

	public function equals(Type $type): bool
	{
		return $type instanceof self && $this->getValue() === $type->getValue();
	}


	public function toInteger(): Type
	{
		return new parent($this->getValue());
	}

	public function generalize(): Type
	{
		return new IntegerType();
	}

	/**
	 * @param mixed[] $properties
	 * @return Type
	 */
	public static function __set_state(array $properties): Type
	{
		return new self($properties['value']);
	}

}
