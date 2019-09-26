<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Type;

class ResolvedMethodReflection implements MethodReflection
{

	/** @var MethodReflection */
	private $reflection;

	/** @var TemplateTypeMap */
	private $resolvedTemplateTypeMap;

	/** @var \PHPStan\Reflection\ParametersAcceptor[]|null */
	private $variants;

	public function __construct(MethodReflection $reflection, TemplateTypeMap $resolvedTemplateTypeMap)
	{
		$this->reflection = $reflection;
		$this->resolvedTemplateTypeMap = $resolvedTemplateTypeMap;
	}

	public function getName(): string
	{
		return $this->reflection->getName();
	}

	public function getPrototype(): ClassMemberReflection
	{
		return $this->reflection->getPrototype();
	}

	/**
	 * @return \PHPStan\Reflection\ParametersAcceptor[]
	 */
	public function getVariants(): array
	{
		$variants = $this->variants;
		if ($variants !== null) {
			return $variants;
		}

		$variants = [];
		foreach ($this->reflection->getVariants() as $variant) {
			$variants[] = new ResolvedFunctionVariant(
				$variant,
				$this->resolvedTemplateTypeMap
			);
		}

		$this->variants = $variants;

		return $variants;
	}

	public function getDeclaringClass(): ClassReflection
	{
		return $this->reflection->getDeclaringClass();
	}

	public function getDeclaringTrait(): ?ClassReflection
	{
		if ($this->reflection instanceof PhpMethodReflection) {
			return $this->reflection->getDeclaringTrait();
		}

		return null;
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

	/** @return string|false */
	public function getDocComment()
	{
		return $this->reflection->getDocComment();
	}

	public function isDeprecated(): TrinaryLogic
	{
		return $this->reflection->isDeprecated();
	}

	public function getDeprecatedDescription(): ?string
	{
		return $this->reflection->getDeprecatedDescription();
	}

	public function isFinal(): TrinaryLogic
	{
		return $this->reflection->isFinal();
	}

	public function isInternal(): TrinaryLogic
	{
		return $this->reflection->isInternal();
	}

	public function getThrowType(): ?Type
	{
		return $this->reflection->getThrowType();
	}

	public function hasSideEffects(): TrinaryLogic
	{
		return $this->reflection->hasSideEffects();
	}

}
