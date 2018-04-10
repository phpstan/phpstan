<?php declare(strict_types = 1);

namespace PHPStan\PhpDoc;

use PHPStan\PhpDoc\Tag\ReturnTag;

class ResolvedPhpDocBlock
{

	/** @var array<string, \PHPStan\PhpDoc\Tag\VarTag> */
	private $varTags;

	/** @var array<string, \PHPStan\PhpDoc\Tag\MethodTag> */
	private $methodTags;

	/** @var array<string, \PHPStan\PhpDoc\Tag\PropertyTag> */
	private $propertyTags;

	/** @var array<string, \PHPStan\PhpDoc\Tag\ParamTag> */
	private $paramTags;

	/** @var \PHPStan\PhpDoc\Tag\ReturnTag|null */
	private $returnTag;

	/** @var bool */
	private $isDeprecated;

	/**
	 * @param array<string, \PHPStan\PhpDoc\Tag\VarTag> $varTags
	 * @param array<string, \PHPStan\PhpDoc\Tag\MethodTag> $methodTags
	 * @param array<string, \PHPStan\PhpDoc\Tag\PropertyTag> $propertyTags
	 * @param array<string, \PHPStan\PhpDoc\Tag\ParamTag> $paramTags
	 * @param \PHPStan\PhpDoc\Tag\ReturnTag|null $returnTag
	 * @param bool $isDeprecated
	 */
	private function __construct(
		array $varTags,
		array $methodTags,
		array $propertyTags,
		array $paramTags,
		?ReturnTag $returnTag,
		bool $isDeprecated
	)
	{
		$this->varTags = $varTags;
		$this->methodTags = $methodTags;
		$this->propertyTags = $propertyTags;
		$this->paramTags = $paramTags;
		$this->returnTag = $returnTag;
		$this->isDeprecated = $isDeprecated;
	}

	/**
	 * @param array<string, \PHPStan\PhpDoc\Tag\VarTag> $varTags
	 * @param array<string, \PHPStan\PhpDoc\Tag\MethodTag> $methodTags
	 * @param array<string, \PHPStan\PhpDoc\Tag\PropertyTag> $propertyTags
	 * @param array<string, \PHPStan\PhpDoc\Tag\ParamTag> $paramTags
	 * @param \PHPStan\PhpDoc\Tag\ReturnTag|null $returnTag
	 * @param bool $isDeprecated
	 * @return self
	 */
	public static function create(
		array $varTags,
		array $methodTags,
		array $propertyTags,
		array $paramTags,
		?ReturnTag $returnTag,
		bool $isDeprecated
	): self
	{
		return new self($varTags, $methodTags, $propertyTags, $paramTags, $returnTag, $isDeprecated);
	}

	public static function createEmpty(): self
	{
		return new self([], [], [], [], null, false);
	}


	/**
	 * @return array<string, \PHPStan\PhpDoc\Tag\VarTag>
	 */
	public function getVarTags(): array
	{
		return $this->varTags;
	}

	/**
	 * @return array<string, \PHPStan\PhpDoc\Tag\MethodTag>
	 */
	public function getMethodTags(): array
	{
		return $this->methodTags;
	}

	/**
	 * @return array<string, \PHPStan\PhpDoc\Tag\PropertyTag>
	 */
	public function getPropertyTags(): array
	{
		return $this->propertyTags;
	}

	/**
	 * @return array<string, \PHPStan\PhpDoc\Tag\ParamTag>
	 */
	public function getParamTags(): array
	{
		return $this->paramTags;
	}

	public function getReturnTag(): ?\PHPStan\PhpDoc\Tag\ReturnTag
	{
		return $this->returnTag;
	}

	public function isDeprecated(): bool
	{
		return $this->isDeprecated;
	}

	/**
	 * @param mixed[] $properties
	 * @return self
	 */
	public static function __set_state(array $properties): self
	{
		return new self(
			$properties['varTags'],
			$properties['methodTags'],
			$properties['propertyTags'],
			$properties['paramTags'],
			$properties['returnTag'],
			$properties['isDeprecated']
		);
	}

}
