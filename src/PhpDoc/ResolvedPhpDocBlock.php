<?php declare(strict_types = 1);

namespace PHPStan\PhpDoc;

use PHPStan\PhpDoc\Tag\ReturnTag;
use PHPStan\PhpDoc\Tag\ThrowsTag;

class ResolvedPhpDocBlock
{

	/** @var array<string|int, \PHPStan\PhpDoc\Tag\VarTag> */
	private $varTags;

	/** @var array<string, \PHPStan\PhpDoc\Tag\MethodTag> */
	private $methodTags;

	/** @var array<string, \PHPStan\PhpDoc\Tag\PropertyTag> */
	private $propertyTags;

	/** @var array<string, \PHPStan\PhpDoc\Tag\ParamTag> */
	private $paramTags;

	/** @var \PHPStan\PhpDoc\Tag\ReturnTag|null */
	private $returnTag;

	/** @var \PHPStan\PhpDoc\Tag\ThrowsTag|null */
	private $throwsTag;

	/** @var bool */
	private $isDeprecated;

	/** @var bool */
	private $isInternal;

	/** @var bool */
	private $isFinal;

	/**
	 * @param array<string|int, \PHPStan\PhpDoc\Tag\VarTag> $varTags
	 * @param array<string, \PHPStan\PhpDoc\Tag\MethodTag> $methodTags
	 * @param array<string, \PHPStan\PhpDoc\Tag\PropertyTag> $propertyTags
	 * @param array<string, \PHPStan\PhpDoc\Tag\ParamTag> $paramTags
	 * @param \PHPStan\PhpDoc\Tag\ReturnTag|null $returnTag
	 * @param \PHPStan\PhpDoc\Tag\ThrowsTag|null $throwsTags
	 * @param bool $isDeprecated
	 * @param bool $isInternal
	 * @param bool $isFinal
	 */
	private function __construct(
		array $varTags,
		array $methodTags,
		array $propertyTags,
		array $paramTags,
		?ReturnTag $returnTag,
		?ThrowsTag $throwsTags,
		bool $isDeprecated,
		bool $isInternal,
		bool $isFinal
	)
	{
		$this->varTags = $varTags;
		$this->methodTags = $methodTags;
		$this->propertyTags = $propertyTags;
		$this->paramTags = $paramTags;
		$this->returnTag = $returnTag;
		$this->throwsTag = $throwsTags;
		$this->isDeprecated = $isDeprecated;
		$this->isInternal = $isInternal;
		$this->isFinal = $isFinal;
	}

	/**
	 * @param array<string|int, \PHPStan\PhpDoc\Tag\VarTag> $varTags
	 * @param array<string, \PHPStan\PhpDoc\Tag\MethodTag> $methodTags
	 * @param array<string, \PHPStan\PhpDoc\Tag\PropertyTag> $propertyTags
	 * @param array<string, \PHPStan\PhpDoc\Tag\ParamTag> $paramTags
	 * @param \PHPStan\PhpDoc\Tag\ReturnTag|null $returnTag
	 * @param \PHPStan\PhpDoc\Tag\ThrowsTag|null $throwsTag
	 * @param bool $isDeprecated
	 * @param bool $isInternal
	 * @param bool $isFinal
	 * @return self
	 */
	public static function create(
		array $varTags,
		array $methodTags,
		array $propertyTags,
		array $paramTags,
		?ReturnTag $returnTag,
		?ThrowsTag $throwsTag,
		bool $isDeprecated,
		bool $isInternal,
		bool $isFinal
	): self
	{
		return new self(
			$varTags,
			$methodTags,
			$propertyTags,
			$paramTags,
			$returnTag,
			$throwsTag,
			$isDeprecated,
			$isInternal,
			$isFinal
		);
	}

	public static function createEmpty(): self
	{
		return new self([], [], [], [], null, null, false, false, false);
	}


	/**
	 * @return array<string|int, \PHPStan\PhpDoc\Tag\VarTag>
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

	public function getThrowsTag(): ?\PHPStan\PhpDoc\Tag\ThrowsTag
	{
		return $this->throwsTag;
	}

	public function isDeprecated(): bool
	{
		return $this->isDeprecated;
	}

	public function isInternal(): bool
	{
		return $this->isInternal;
	}

	public function isFinal(): bool
	{
		return $this->isFinal;
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
			$properties['throwsTag'],
			$properties['isDeprecated'],
			$properties['isInternal'],
			$properties['isFinal']
		);
	}

}
