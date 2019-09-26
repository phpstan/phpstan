<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Reflection\Php\DummyParameter;
use PHPStan\Type\Generic\TemplateTypeHelper;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Type;

class ResolvedFunctionVariant implements ParametersAcceptor
{

	/** @var ParametersAcceptor */
	private $parametersAcceptor;

	/** @var TemplateTypeMap */
	private $resolvedTemplateTypeMap;

	/** @var ParameterReflection[]|null */
	private $parameters;

	/** @var Type|null */
	private $returnType;

	public function __construct(
		ParametersAcceptor $parametersAcceptor,
		TemplateTypeMap $resolvedTemplateTypeMap
	)
	{
		$this->parametersAcceptor = $parametersAcceptor;
		$this->resolvedTemplateTypeMap = $resolvedTemplateTypeMap;
	}

	public function getTemplateTypeMap(): TemplateTypeMap
	{
		return $this->parametersAcceptor->getTemplateTypeMap();
	}

	public function getResolvedTemplateTypeMap(): TemplateTypeMap
	{
		return $this->resolvedTemplateTypeMap;
	}

	public function getParameters(): array
	{
		$parameters = $this->parameters;

		if ($parameters === null) {
			$parameters = array_map(function (ParameterReflection $param): ParameterReflection {
				return new DummyParameter(
					$param->getName(),
					TemplateTypeHelper::resolveTemplateTypes($param->getType(), $this->resolvedTemplateTypeMap),
					$param->isOptional(),
					$param->passedByReference(),
					$param->isVariadic(),
					$param->getDefaultValue()
				);
			}, $this->parametersAcceptor->getParameters());

			$this->parameters = $parameters;
		}

		return $parameters;
	}

	public function isVariadic(): bool
	{
		return $this->parametersAcceptor->isVariadic();
	}

	public function getReturnType(): Type
	{
		$type = $this->returnType;

		if ($type === null) {
			$type = TemplateTypeHelper::resolveTemplateTypes(
				$this->parametersAcceptor->getReturnType(),
				$this->resolvedTemplateTypeMap
			);

			$this->returnType = $type;
		}

		return $type;
	}

}
