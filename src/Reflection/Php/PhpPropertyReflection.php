<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\DeprecatableReflection;
use PHPStan\Reflection\InternableReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypehintHelper;

class PhpPropertyReflection implements PropertyReflection, DeprecatableReflection, InternableReflection
{

	/** @var \PHPStan\Reflection\ClassReflection */
	private $declaringClass;

	/** @var \ReflectionType|null */
	private $nativeType;

	/** @var \PHPStan\Type\Type|null */
	private $finalNativeType;

	/** @var \PHPStan\Type\Type|null */
	private $phpDocType;

	/** @var \PHPStan\Type\Type|null */
	private $type;

	/** @var \ReflectionProperty */
	private $reflection;

	/** @var string|null */
	private $deprecatedDescription;

	/** @var bool */
	private $isDeprecated;

	/** @var bool */
	private $isInternal;

	public function __construct(
		ClassReflection $declaringClass,
		?\ReflectionType $nativeType,
		?Type $phpDocType,
		\ReflectionProperty $reflection,
		?string $deprecatedDescription,
		bool $isDeprecated,
		bool $isInternal
	)
	{
		$this->declaringClass = $declaringClass;
		$this->nativeType = $nativeType;
		$this->phpDocType = $phpDocType;
		$this->reflection = $reflection;
		$this->deprecatedDescription = $deprecatedDescription;
		$this->isDeprecated = $isDeprecated;
		$this->isInternal = $isInternal;
	}

	public function getDeclaringClass(): ClassReflection
	{
		return $this->declaringClass;
	}

	/**
	 * @return string|false
	 */
	public function getDocComment()
	{
		return $this->reflection->getDocComment();
	}

	public function isStatic(): bool
	{
		return $this->reflection->isStatic();
	}

	public function isPrivate(): bool
	{
		return $this->reflection->isPrivate();
	}

	public function isPublic(): bool
	{
		return $this->reflection->isPublic();
	}

	public function getType(): Type
	{
		if ($this->type === null) {
			$phpDocType = $this->phpDocType;
			if (
				$this->nativeType !== null
				&& $this->phpDocType !== null
				&& $this->nativeType->allowsNull() !== TypeCombinator::containsNull($this->phpDocType)
			) {
				$phpDocType = null;
			}
			$this->type = TypehintHelper::decideTypeFromReflection(
				$this->nativeType,
				$phpDocType,
				$this->declaringClass->getName()
			);
		}

		return $this->type;
	}

	public function hasPhpDoc(): bool
	{
		return $this->phpDocType !== null;
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
		if ($this->finalNativeType === null) {
			$this->finalNativeType = TypehintHelper::decideTypeFromReflection(
				$this->nativeType,
				null,
				$this->declaringClass->getName()
			);
		}

		return $this->finalNativeType;
	}

	public function isReadable(): bool
	{
		return true;
	}

	public function isWritable(): bool
	{
		return true;
	}

	public function getDeprecatedDescription(): ?string
	{
		if ($this->isDeprecated) {
			return $this->deprecatedDescription;
		}

		return null;
	}

	public function isDeprecated(): bool
	{
		return $this->isDeprecated;
	}

	public function isInternal(): bool
	{
		return $this->isInternal;
	}

}
