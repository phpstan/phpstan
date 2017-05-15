<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Annotations;

use PHPStan\Reflection\ParameterReflection;
use PHPStan\Type\Type;

class AnnotationsMethodParameterReflection implements ParameterReflection
{

	/** @var string */
	private $name;

	/** @var Type */
	private $type;

	/** @var bool */
	private $isPassedByReference;

	/** @var bool */
	private $isOptional;

	/** @var bool */
	private $isVariadic;

	public function __construct(string $name, Type $type, bool $isPassedByReference, bool $isOptional, bool $isVariadic)
	{
		$this->name = $name;
		$this->type = $type;
		$this->isPassedByReference = $isPassedByReference;
		$this->isOptional = $isOptional;
		$this->isVariadic = $isVariadic;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function isOptional(): bool
	{
		return $this->isOptional;
	}

	public function getType(): Type
	{
		return $this->type;
	}

	public function isPassedByReference(): bool
	{
		return $this->isPassedByReference;
	}

	public function isVariadic(): bool
	{
		return $this->isVariadic;
	}

}
