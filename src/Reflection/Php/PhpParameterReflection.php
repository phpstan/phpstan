<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PHPStan\Reflection\ParameterReflection;
use PHPStan\Type\Type;
use PHPStan\Type\TypehintHelper;

class PhpParameterReflection implements ParameterReflection
{

	/** @var \ReflectionParameter */
	private $reflection;

	/** @var \PHPStan\Type\Type|null */
	private $phpDocType = null;

	/** @var \PHPStan\Type\Type */
	private $type;

	public function __construct(\ReflectionParameter $reflection, Type $phpDocType = null)
	{
		$this->reflection = $reflection;
		$this->phpDocType = $phpDocType;
	}

	public function isOptional(): bool
	{
		return $this->reflection->isOptional();
	}

	public function getName(): string
	{
		return $this->reflection->getName();
	}

	public function getType(): Type
	{
		if ($this->type === null) {
			$phpDocType = $this->phpDocType;
			if ($phpDocType !== null && $this->reflection->isDefaultValueAvailable() && $this->reflection->getDefaultValue() === null) {
				$phpDocType = $phpDocType->makeNullable();
			}
			$this->type = TypehintHelper::decideTypeFromReflection(
				$this->reflection->getType(),
				$phpDocType,
				$this->reflection->getDeclaringClass() !== null ? $this->reflection->getDeclaringClass()->getName() : null,
				$this->isVariadic()
			);
		}

		return $this->type;
	}

	public function isPassedByReference(): bool
	{
		return $this->reflection->isPassedByReference();
	}

	public function isVariadic(): bool
	{
		return $this->reflection->isVariadic();
	}

}
