<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\PassedByReference;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypehintHelper;

class PhpFunctionFromParserNodeReflection implements \PHPStan\Reflection\FunctionReflection
{

	/** @var \PhpParser\Node\FunctionLike */
	private $functionLike;

	/** @var \PHPStan\Type\Type[] */
	private $realParameterTypes;

	/** @var \PHPStan\Type\Type[] */
	private $phpDocParameterTypes;

	/** @var bool */
	private $realReturnTypePresent;

	/** @var \PHPStan\Type\Type */
	private $realReturnType;

	/** @var \PHPStan\Type\Type|null */
	private $phpDocReturnType;

	/** @var bool */
	private $isVariadic;

	/** @var \PHPStan\Reflection\Php\PhpParameterFromParserNodeReflection[] */
	private $parameters;

	/** @var \PHPStan\Type\Type */
	private $returnType;

	/** @var \PHPStan\Type\Type|null */
	private $throwType;

	/** @var bool */
	private $isDeprecated;

	/**
	 * @param FunctionLike $functionLike
	 * @param \PHPStan\Type\Type[] $realParameterTypes
	 * @param \PHPStan\Type\Type[] $phpDocParameterTypes
	 * @param bool $realReturnTypePresent
	 * @param Type $realReturnType
	 * @param null|Type $phpDocReturnType
	 * @param null|Type $throwType
	 * @param bool $isDeprecated
	 */
	public function __construct(
		FunctionLike $functionLike,
		array $realParameterTypes,
		array $phpDocParameterTypes,
		bool $realReturnTypePresent,
		Type $realReturnType,
		?Type $phpDocReturnType = null,
		?Type $throwType = null,
		bool $isDeprecated = false
	)
	{
		$this->functionLike = $functionLike;
		$this->realParameterTypes = $realParameterTypes;
		$this->phpDocParameterTypes = $phpDocParameterTypes;
		$this->realReturnTypePresent = $realReturnTypePresent;
		$this->realReturnType = $realReturnType;
		$this->phpDocReturnType = $phpDocReturnType;
		$this->throwType = $throwType;
		$this->isDeprecated = $isDeprecated;
	}

	protected function getFunctionLike(): FunctionLike
	{
		return $this->functionLike;
	}

	public function getName(): string
	{
		if ($this->functionLike instanceof ClassMethod) {
			return $this->functionLike->name->name;
		}

		return (string) $this->functionLike->namespacedName;
	}

	/**
	 * @return \PHPStan\Reflection\ParametersAcceptor[]
	 */
	public function getVariants(): array
	{
		return [
			new FunctionVariant(
				$this->getParameters(),
				$this->isVariadic(),
				$this->getReturnType()
			),
		];
	}

	/**
	 * @return \PHPStan\Reflection\ParameterReflection[]
	 */
	private function getParameters(): array
	{
		if ($this->parameters === null) {
			$parameters = [];
			$isOptional = true;
			/** @var \PhpParser\Node\Param $parameter */
			foreach (array_reverse($this->functionLike->getParams()) as $parameter) {
				if (!$isOptional || $parameter->default === null) {
					$isOptional = false;
				}

				if (!is_string($parameter->var->name)) {
					throw new \PHPStan\ShouldNotHappenException();
				}
				$parameters[] = new PhpParameterFromParserNodeReflection(
					$parameter->var->name,
					$isOptional,
					$this->realParameterTypes[$parameter->var->name],
					isset($this->phpDocParameterTypes[$parameter->var->name]) ? $this->phpDocParameterTypes[$parameter->var->name] : null,
					$parameter->byRef
						? PassedByReference::createCreatesNewVariable()
						: PassedByReference::createNo(),
					$parameter->default,
					$parameter->variadic
				);
			}

			$this->parameters = array_reverse($parameters);
		}

		return $this->parameters;
	}

	private function isVariadic(): bool
	{
		if ($this->isVariadic === null) {
			$isVariadic = false;
			foreach ($this->functionLike->getParams() as $parameter) {
				if ($parameter->variadic) {
					$isVariadic = true;
					break;
				}
			}

			$this->isVariadic = $isVariadic;
		}

		return $this->isVariadic;
	}

	protected function getReturnType(): Type
	{
		if ($this->returnType === null) {
			$phpDocReturnType = $this->phpDocReturnType;
			if (
				$this->realReturnTypePresent
				&& $phpDocReturnType !== null
				&& TypeCombinator::containsNull($this->realReturnType) !== TypeCombinator::containsNull($phpDocReturnType)
			) {
				$phpDocReturnType = null;
			}
			$this->returnType = TypehintHelper::decideType($this->realReturnType, $phpDocReturnType);
		}

		return $this->returnType;
	}

	public function isDeprecated(): bool
	{
		return $this->isDeprecated;
	}

	public function getThrowType(): ?Type
	{
		return $this->throwType;
	}

}
