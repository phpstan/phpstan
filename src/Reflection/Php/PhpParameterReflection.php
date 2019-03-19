<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PHPStan\Reflection\ParameterReflectionWithPhpDocs;
use PHPStan\Reflection\PassedByReference;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypehintHelper;

class PhpParameterReflection implements ParameterReflectionWithPhpDocs
{

	/** @var \ReflectionParameter */
	private $reflection;

	/** @var \PHPStan\Type\Type|null */
	private $phpDocType = null;

	/** @var \PHPStan\Type\Type|null */
	private $type;

	/** @var \PHPStan\Type\Type|null */
	private $nativeType;

	public function __construct(\ReflectionParameter $reflection, ?Type $phpDocType)
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
				$phpDocType = \PHPStan\Type\TypeCombinator::addNull($phpDocType);
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

	public function passedByReference(): PassedByReference
	{
		return $this->reflection->isPassedByReference()
			? PassedByReference::createCreatesNewVariable()
			: PassedByReference::createNo();
	}

	public function isVariadic(): bool
	{
		return $this->reflection->isVariadic();
	}

	public function getPhpDocType(): Type
	{
		if ($this->phpDocType !== null) {
			return $this->phpDocType;
		}

		return new MixedType();
	}

	public function getNativeType(): Type
	{
		if ($this->nativeType === null) {
			$this->nativeType = TypehintHelper::decideTypeFromReflection(
				$this->reflection->getType(),
				null,
				$this->reflection->getDeclaringClass() !== null ? $this->reflection->getDeclaringClass()->getName() : null,
				$this->isVariadic()
			);
		}

		return $this->nativeType;
	}

}
