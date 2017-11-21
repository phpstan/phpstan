<?php declare(strict_types = 1);

namespace PHPStan\PhpDoc\Tag;

use PHPStan\Type\Type;

class MethodTagParameter
{

	/** @var \PHPStan\Type\Type */
	private $type;

	/** @var bool */
	private $isPassedByReference;

	/** @var bool */
	private $isOptional;

	/** @var bool */
	private $isVariadic;

	public function __construct(
		Type $type,
		bool $isPassedByReference,
		bool $isOptional,
		bool $isVariadic
	)
	{
		$this->type = $type;
		$this->isPassedByReference = $isPassedByReference;
		$this->isOptional = $isOptional;
		$this->isVariadic = $isVariadic;
	}

	public function getType(): Type
	{
		return $this->type;
	}

	public function isPassedByReference(): bool
	{
		return $this->isPassedByReference;
	}

	public function isOptional(): bool
	{
		return $this->isOptional;
	}

	public function isVariadic(): bool
	{
		return $this->isVariadic;
	}

	public static function __set_state(array $properties): self
	{
		return new self(
			$properties['type'],
			$properties['isPassedByReference'],
			$properties['isOptional'],
			$properties['isVariadic']
		);
	}

}
