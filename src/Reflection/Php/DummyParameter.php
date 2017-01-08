<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PHPStan\Reflection\ParameterReflection;
use PHPStan\Type\Type;

class DummyParameter implements ParameterReflection
{

	/** @var string */
	private $name;

	/** @var \PHPStan\Type\Type  */
	private $type;

	/** @var bool */
	private $optional;

	/** @var bool */
	private $passedByReference;

	/** @var bool */
	private $variadic;

	public function __construct(string $name, Type $type, bool $optional, bool $passedByReference = false, bool $variadic = false)
	{
		$this->name = $name;
		$this->type = $type;
		$this->optional = $optional;
		$this->passedByReference = $passedByReference;
		$this->variadic = $variadic;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function isOptional(): bool
	{
		return $this->optional;
	}

	public function getType(): Type
	{
		return $this->type;
	}

	public function isPassedByReference(): bool
	{
		return $this->passedByReference;
	}

	public function isVariadic(): bool
	{
		return $this->variadic;
	}

}
