<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Annotations;

use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\PassedByReference;
use PHPStan\Type\Type;

class AnnotationsMethodParameterReflection implements ParameterReflection
{

	/** @var string */
	private $name;

	/** @var Type */
	private $type;

	/** @var \PHPStan\Reflection\PassedByReference */
	private $passedByReference;

	/** @var bool */
	private $isOptional;

	/** @var bool */
	private $isVariadic;

	/** @var Type|null */
	private $defaultValue;

	public function __construct(string $name, Type $type, PassedByReference $passedByReference, bool $isOptional, bool $isVariadic, ?Type $defaultValue)
	{
		$this->name = $name;
		$this->type = $type;
		$this->passedByReference = $passedByReference;
		$this->isOptional = $isOptional;
		$this->isVariadic = $isVariadic;
		$this->defaultValue = $defaultValue;
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

	public function passedByReference(): PassedByReference
	{
		return $this->passedByReference;
	}

	public function isVariadic(): bool
	{
		return $this->isVariadic;
	}

	public function getDefaultValue(): ?Type
	{
		return $this->defaultValue;
	}

}
