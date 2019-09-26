<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypehintHelper;

class PhpPropertyReflection implements PropertyReflection
{

	/** @var \PHPStan\Reflection\ClassReflection */
	private $declaringClass;

	/** @var \PHPStan\Reflection\ClassReflection|null */
	private $declaringTrait;

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
		?ClassReflection $declaringTrait,
		?\ReflectionType $nativeType,
		?Type $phpDocType,
		\ReflectionProperty $reflection,
		?string $deprecatedDescription,
		bool $isDeprecated,
		bool $isInternal
	)
	{
		$this->declaringClass = $declaringClass;
		$this->declaringTrait = $declaringTrait;
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

	public function getDeclaringTrait(): ?ClassReflection
	{
		return $this->declaringTrait;
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

	public function getReadableType(): Type
	{
		if ($this->type === null) {
			$this->type = TypehintHelper::decideTypeFromReflection(
				$this->nativeType,
				$this->phpDocType,
				$this->declaringClass->getName()
			);
		}

		return $this->type;
	}

	public function getWritableType(): Type
	{
		return $this->getReadableType();
	}

	public function canChangeTypeAfterAssignment(): bool
	{
		return true;
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

	public function isDeprecated(): TrinaryLogic
	{
		return TrinaryLogic::createFromBoolean($this->isDeprecated);
	}

	public function isInternal(): TrinaryLogic
	{
		return TrinaryLogic::createFromBoolean($this->isInternal);
	}

}
