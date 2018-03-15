<?php declare(strict_types = 1);

namespace PHPStan\PhpDoc;

use PHPStan\PhpDoc\Tag\ReturnTag;

class ResolvedPhpDocBlock
{

	/** @var \PHPStan\PhpDoc\Tag\VarTag[] */
	private $varTags;

	/** @var \PHPStan\PhpDoc\Tag\MethodTag[] */
	private $methodTags;

	/** @var \PHPStan\PhpDoc\Tag\PropertyTag[] */
	private $propertyTags;

	/** @var \PHPStan\PhpDoc\Tag\ParamTag[] */
	private $paramTags;

	/** @var \PHPStan\PhpDoc\Tag\ReturnTag|null */
	private $returnTag;

	/**
	 * @param \PHPStan\PhpDoc\Tag\VarTag[] $varTags
	 * @param \PHPStan\PhpDoc\Tag\MethodTag[] $methodTags
	 * @param \PHPStan\PhpDoc\Tag\PropertyTag[] $propertyTags
	 * @param \PHPStan\PhpDoc\Tag\ParamTag[] $paramTags
	 * @param \PHPStan\PhpDoc\Tag\ReturnTag|null $returnTag
	 */
	private function __construct(
		array $varTags,
		array $methodTags,
		array $propertyTags,
		array $paramTags,
		?ReturnTag $returnTag
	)
	{
		$this->varTags = $varTags;
		$this->methodTags = $methodTags;
		$this->propertyTags = $propertyTags;
		$this->paramTags = $paramTags;
		$this->returnTag = $returnTag;
	}

	/**
	 * @param \PHPStan\PhpDoc\Tag\VarTag[] $varTags
	 * @param \PHPStan\PhpDoc\Tag\MethodTag[] $methodTags
	 * @param \PHPStan\PhpDoc\Tag\PropertyTag[] $propertyTags
	 * @param \PHPStan\PhpDoc\Tag\ParamTag[] $paramTags
	 * @param \PHPStan\PhpDoc\Tag\ReturnTag|null $returnTag
	 * @return self
	 */
	public static function create(
		array $varTags,
		array $methodTags,
		array $propertyTags,
		array $paramTags,
		?ReturnTag $returnTag
	): self
	{
		return new self($varTags, $methodTags, $propertyTags, $paramTags, $returnTag);
	}

	public static function createEmpty(): self
	{
		return new self([], [], [], [], null);
	}


	/**
	 * @return \PHPStan\PhpDoc\Tag\VarTag[]
	 */
	public function getVarTags(): array
	{
		return $this->varTags;
	}

	/**
	 * @return \PHPStan\PhpDoc\Tag\MethodTag[]
	 */
	public function getMethodTags(): array
	{
		return $this->methodTags;
	}

	/**
	 * @return \PHPStan\PhpDoc\Tag\PropertyTag[]
	 */
	public function getPropertyTags(): array
	{
		return $this->propertyTags;
	}

	/**
	 * @return \PHPStan\PhpDoc\Tag\ParamTag[]
	 */
	public function getParamTags(): array
	{
		return $this->paramTags;
	}

	public function getReturnTag(): ?\PHPStan\PhpDoc\Tag\ReturnTag
	{
		return $this->returnTag;
	}

	public static function __set_state(array $properties): self
	{
		return new self(
			$properties['varTags'],
			$properties['methodTags'],
			$properties['propertyTags'],
			$properties['paramTags'],
			$properties['returnTag']
		);
	}

}
