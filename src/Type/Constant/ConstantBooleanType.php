<?php declare(strict_types = 1);

namespace PHPStan\Type\Constant;

use PHPStan\Type\BooleanType;
use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\Traits\ConstantScalarTypeTrait;

class ConstantBooleanType extends BooleanType implements ConstantScalarType
{

	use ConstantScalarTypeTrait;

	/** @var bool */
	private $value;

	public function __construct(bool $value)
	{
		$this->value = $value;
	}

	public function getValue(): bool
	{
		return $this->value;
	}

	public function describe(): string
	{
		return $this->value ? 'true' : 'false';
	}

}
