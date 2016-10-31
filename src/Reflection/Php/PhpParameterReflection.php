<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PHPStan\Reflection\ParameterReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
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
			$phpTypeReflection = $this->reflection->getType();
			if ($phpTypeReflection === null) {
				if ($this->phpDocType !== null) {
					$type = $this->phpDocType;
					if ($this->reflection->isDefaultValueAvailable() && $this->reflection->getDefaultValue() === null) {
						$type = $type->makeNullable();
					}

					$this->type = $type;
				} else {
					$this->type = new MixedType(true);
				}
			} else {
				$typehintType = TypehintHelper::getTypeObjectFromTypehint(
					(string) $phpTypeReflection,
					$phpTypeReflection->allowsNull(),
					$this->reflection->getDeclaringClass() !== null ? $this->reflection->getDeclaringClass()->getName() : null
				);
				if ($this->phpDocType !== null) {
					$phpDocType = $this->phpDocType;
					if ($this->reflection->isDefaultValueAvailable() && $this->reflection->getDefaultValue() === null) {
						$phpDocType = $phpDocType->makeNullable();
					}

					if (
						$typehintType->getClass() !== null
						&& $phpDocType->getClass() !== $typehintType->getClass()
						&& $this->phpDocType->getClass() !== null
					) {
						$phpDocTypeClassReflection = new \ReflectionClass($phpDocType->getClass());
						if ($phpDocTypeClassReflection->isSubclassOf($typehintType->getClass())) {
							return $this->type = $phpDocType;
						} else {
							return new MixedType($typehintType->isNullable() || $phpDocType->isNullable());
						}
					} elseif (
						$typehintType instanceof ArrayType
						&& $phpDocType instanceof ArrayType
					) {
						return $this->type = $phpDocType;
					}
				}

				return $this->type = $typehintType;
			}
		}

		return $this->type;
	}

	public function isPassedByReference(): bool
	{
		return $this->reflection->isPassedByReference();
	}

}
