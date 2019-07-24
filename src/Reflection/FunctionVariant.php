<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Type;

class FunctionVariant implements ParametersAcceptor
{

	/** @var TemplateTypeMap */
	private $templateTypeMap;

	/** @var array<int, ParameterReflection> */
	private $parameters;

	/** @var bool */
	private $isVariadic;

	/** @var Type */
	private $returnType;

	/**
	 * @param array<int, ParameterReflection> $parameters
	 * @param bool $isVariadic
	 * @param Type $returnType
	 */
	public function __construct(
		TemplateTypeMap $templateTypeMap,
		array $parameters,
		bool $isVariadic,
		Type $returnType
	)
	{
		$this->templateTypeMap = $templateTypeMap;
		$this->parameters = $parameters;
		$this->isVariadic = $isVariadic;
		$this->returnType = $returnType;
	}

	public function getTemplateTypeMap(): TemplateTypeMap
	{
		return $this->templateTypeMap;
	}

	/**
	 * @return array<int, ParameterReflection>
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

	public function isVariadic(): bool
	{
		return $this->isVariadic;
	}

	public function getReturnType(): Type
	{
		return $this->returnType;
	}

}
