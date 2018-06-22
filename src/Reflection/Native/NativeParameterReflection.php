<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Native;

use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\PassedByReference;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\Type;

class NativeParameterReflection implements ParameterReflection
{

	/** @var string */
	private $name;

	/** @var bool */
	private $optional;

	/** @var \PHPStan\Type\Type */
	private $type;

	/** @var \PHPStan\Reflection\PassedByReference */
	private $passedByReference;

	/** @var bool */
	private $variadic;

	public function __construct(
		string $name,
		bool $optional,
		Type $type,
		PassedByReference $passedByReference,
		bool $variadic
	)
	{
		$this->name = $name;
		$this->optional = $optional;
		$this->type = $type;
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
		$type = $this->type;
		if ($this->variadic) {
			$type = new ArrayType(new IntegerType(), $type);
		}

		return $type;
	}

	public function passedByReference(): PassedByReference
	{
		return $this->passedByReference;
	}

	public function isVariadic(): bool
	{
		return $this->variadic;
	}

	/**
	 * @param mixed[] $properties
	 * @return self
	 */
	public static function __set_state(array $properties): self
	{
		return new self(
			$properties['name'],
			$properties['optional'],
			$properties['type'],
			$properties['passedByReference'],
			$properties['variadic']
		);
	}

}
