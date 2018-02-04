<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Native;

use PHPStan\Reflection\ParameterReflection;
use PHPStan\Type\Type;

class NativeParameterReflection implements ParameterReflection
{

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var bool
	 */
	private $optional;

	/**
	 * @var \PHPStan\Type\Type
	 */
	private $type;

	/**
	 * @var bool
	 */
	private $passedByReference;

	/**
	 * @var bool
	 */
	private $variadic;

	public function __construct(
		string $name,
		bool $optional,
		Type $type,
		bool $passedByReference,
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
