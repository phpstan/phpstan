<?php declare(strict_types = 1);

namespace PHPStan\PhpDoc\Tag;

use PHPStan\Type\Type;

class MethodTag
{

	/** @var \PHPStan\Type\Type */
	private $returnType;

	/** @var bool */
	private $isStatic;

	/** @var array<string, \PHPStan\PhpDoc\Tag\MethodTagParameter> */
	private $parameters;

	/**
	 * @param \PHPStan\Type\Type $returnType
	 * @param bool $isStatic
	 * @param array<string, \PHPStan\PhpDoc\Tag\MethodTagParameter> $parameters
	 */
	public function __construct(
		Type $returnType,
		bool $isStatic,
		array $parameters
	)
	{
		$this->returnType = $returnType;
		$this->isStatic = $isStatic;
		$this->parameters = $parameters;
	}

	public function getReturnType(): Type
	{
		return $this->returnType;
	}

	public function isStatic(): bool
	{
		return $this->isStatic;
	}

	/**
	 * @return array<string, \PHPStan\PhpDoc\Tag\MethodTagParameter>
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

	/**
	 * @param mixed[] $properties
	 * @return self
	 */
	public static function __set_state(array $properties): self
	{
		return new self(
			$properties['returnType'],
			$properties['isStatic'],
			$properties['parameters']
		);
	}

}
