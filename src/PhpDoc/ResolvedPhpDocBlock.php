<?php declare(strict_types = 1);

namespace PHPStan\PhpDoc;

use PHPStan\PhpDoc\Tag\ReturnTag;

class ResolvedPhpDocBlock
{

	/**
	 * @var \PHPStan\PhpDoc\Tag\VarTag[]
	 */
	private $varTags;

	/**
	 * @var \PHPStan\PhpDoc\Tag\MethodTag[]
	 */
	private $methodTags;

	/**
	 * @var \PHPStan\PhpDoc\Tag\PropertyTag[]
	 */
	private $propertyTags;

	/**
	 * @var \PHPStan\PhpDoc\Tag\ParamTag[]
	 */
	private $paramTags;

	/**
	 * @var \PHPStan\PhpDoc\Tag\ReturnTag|null
	 */
	private $returnTag;

	/**
	 * @param \PHPStan\PhpDoc\Tag\VarTag[] $varTags
	 * @param \PHPStan\PhpDoc\Tag\MethodTag[] $methodTags
	 * @param \PHPStan\PhpDoc\Tag\PropertyTag[] $propertyTags
	 * @param \PHPStan\PhpDoc\Tag\ParamTag[] $paramTags
	 * @param \PHPStan\PhpDoc\Tag\ReturnTag|null $returnTag
	 */
	public function __construct(
		array $varTags,
		array $methodTags,
		array $propertyTags,
		array $paramTags,
		ReturnTag $returnTag = null
	)
	{
		$this->varTags = $varTags;
		$this->methodTags = $methodTags;
		$this->propertyTags = $propertyTags;
		$this->paramTags = $paramTags;
		$this->returnTag = $returnTag;
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

	/**
	 * @return \PHPStan\PhpDoc\Tag\ReturnTag|null
	 */
	public function getReturnTag()
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
