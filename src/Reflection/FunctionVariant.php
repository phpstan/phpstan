<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Type;

class FunctionVariant implements ParametersAcceptor
{

	/** @var TemplateTypeMap */
	private $templateTypeMap;

	/** @var TemplateTypeMap|null */
	private $resolvedTemplateTypeMap;

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
		?TemplateTypeMap $resolvedTemplateTypeMap,
		array $parameters,
		bool $isVariadic,
		Type $returnType
	)
	{
		$this->templateTypeMap = $templateTypeMap;
		$this->resolvedTemplateTypeMap = $resolvedTemplateTypeMap;
		$this->parameters = $parameters;
		$this->isVariadic = $isVariadic;
		$this->returnType = $returnType;
	}

	public function getTemplateTypeMap(): TemplateTypeMap
	{
		return $this->templateTypeMap;
	}

	public function getResolvedTemplateTypeMap(): TemplateTypeMap
	{
		return $this->resolvedTemplateTypeMap ?? TemplateTypeMap::createEmpty();
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
