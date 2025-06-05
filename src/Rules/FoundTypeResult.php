<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PHPStan\Type\Type;

class FoundTypeResult
{

	/** @var \PHPStan\Type\Type */
	private $type;

	/** @var string[] */
	private $referencedClasses;

	/** @var array<mixed>|\PHPStan\Rules\RuleError[] */
	private $unknownClassErrors;

	/**
	 * @param \PHPStan\Type\Type $type
	 * @param string[] $referencedClasses
	 * @param array<mixed>|\PHPStan\Rules\RuleError[] $unknownClassErrors
	 */
	public function __construct(
		Type $type,
		array $referencedClasses,
		array $unknownClassErrors
	)
	{
		$this->type = $type;
		$this->referencedClasses = $referencedClasses;
		$this->unknownClassErrors = $unknownClassErrors;
	}

	public function getType(): Type
	{
		return $this->type;
	}

	/**
	 * @return string[]
	 */
	public function getReferencedClasses(): array
	{
		return $this->referencedClasses;
	}

	/**
	 * @return array<mixed>|\PHPStan\Rules\RuleError[]
	 */
	public function getUnknownClassErrors(): array
	{
		return $this->unknownClassErrors;
	}

}
