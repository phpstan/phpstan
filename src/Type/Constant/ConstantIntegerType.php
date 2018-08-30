<?php declare(strict_types = 1);

namespace PHPStan\Type\Constant;

use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\Traits\ConstantScalarTypeTrait;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class ConstantIntegerType extends IntegerType implements ConstantScalarType
{

	use ConstantScalarTypeTrait;
	use ConstantScalarToBooleanTrait;

	/** @var int */
	private $value;

	public function __construct(int $value)
	{
		$this->value = $value;
	}

	public function getValue(): int
	{
		return $this->value;
	}

	public function describe(VerbosityLevel $level): string
	{
		return $level->handle(
			static function (): string {
				return 'int';
			},
			function (): string {
				return sprintf('%s', $this->value);
			}
		);
	}

	public function toFloat(): Type
	{
		return new ConstantFloatType($this->value);
	}

	public function toString(): Type
	{
		return new ConstantStringType((string) $this->value);
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
