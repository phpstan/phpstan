<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\FunctionVariantWithPhpDocs;
use PHPStan\Reflection\PassedByReference;
use PHPStan\Type\MixedType;
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

	/** @var \PHPStan\Type\Type|null */
	private $throwType;

	/** @var string|null */
	private $deprecatedDescription;

	/** @var bool */
	private $isDeprecated;

	/** @var bool */
	private $isInternal;

	/** @var bool */
	private $isFinal;

	/** @var FunctionVariantWithPhpDocs[]|null */
	private $variants;

	/**
	 * @param FunctionLike $functionLike
	 * @param \PHPStan\Type\Type[] $realParameterTypes
	 * @param \PHPStan\Type\Type[] $phpDocParameterTypes
	 * @param bool $realReturnTypePresent
	 * @param Type $realReturnType
	 * @param Type|null $phpDocReturnType
	 * @param Type|null $throwType
	 * @param string|null $deprecatedDescription
	 * @param bool $isDeprecated
	 * @param bool $isInternal
	 * @param bool $isFinal
	 */
	public function __construct(
		FunctionLike $functionLike,
		array $realParameterTypes,
		array $phpDocParameterTypes,
		bool $realReturnTypePresent,
		Type $realReturnType,
		?Type $phpDocReturnType = null,
		?Type $throwType = null,
		?string $deprecatedDescription = null,
		bool $isDeprecated = false,
		bool $isInternal = false,
		bool $isFinal = false
	)
	{
		$this->functionLike = $functionLike;
		$this->realParameterTypes = $realParameterTypes;
		$this->phpDocParameterTypes = $phpDocParameterTypes;
		$this->realReturnTypePresent = $realReturnTypePresent;
		$this->realReturnType = $realReturnType;
		$this->phpDocReturnType = $phpDocReturnType;
		$this->throwType = $throwType;
		$this->deprecatedDescription = $deprecatedDescription;
		$this->isDeprecated = $isDeprecated;
		$this->isInternal = $isInternal;
		$this->isFinal = $isFinal;
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
	 * @return \PHPStan\Reflection\ParametersAcceptorWithPhpDocs[]
	 */
	public function getVariants(): array
	{
		if ($this->variants === null) {
			$this->variants = [
				new FunctionVariantWithPhpDocs(
					$this->getParameters(),
					$this->isVariadic(),
					$this->getReturnType(),
					$this->phpDocReturnType ?? new MixedType(),
					$this->realReturnType ?? new MixedType()
				),
			];
		}

		return $this->variants;
	}

	/**
	 * @return \PHPStan\Reflection\ParameterReflectionWithPhpDocs[]
	 */
	private function getParameters(): array
	{
		$parameters = [];
		$isOptional = true;

		/** @var \PhpParser\Node\Param $parameter */
		foreach (array_reverse($this->functionLike->getParams()) as $parameter) {
			if (!$isOptional || $parameter->default === null) {
				$isOptional = false;
			}

			if (!$parameter->var instanceof Variable || !is_string($parameter->var->name)) {
				throw new \PHPStan\ShouldNotHappenException();
			}
			$parameters[] = new PhpParameterFromParserNodeReflection(
				$parameter->var->name,
				$isOptional,
				$this->realParameterTypes[$parameter->var->name],
				$this->phpDocParameterTypes[$parameter->var->name] ?? null,
				$parameter->byRef
					? PassedByReference::createCreatesNewVariable()
					: PassedByReference::createNo(),
				$parameter->default,
				$parameter->variadic
			);
		}

		return array_reverse($parameters);
	}

	private function isVariadic(): bool
	{
		foreach ($this->functionLike->getParams() as $parameter) {
			if ($parameter->variadic) {
				return true;
			}
		}

		return false;
	}

	protected function getReturnType(): Type
	{
		$phpDocReturnType = $this->phpDocReturnType;
		if (
			$this->realReturnTypePresent
			&& $phpDocReturnType !== null
			&& TypeCombinator::containsNull($this->realReturnType) !== TypeCombinator::containsNull($phpDocReturnType)
		) {
			$phpDocReturnType = null;
		}
		return TypehintHelper::decideType($this->realReturnType, $phpDocReturnType);
	}

	public function getDeprecatedDescription(): ?string
	{
		if ($this->isDeprecated) {
			return $this->deprecatedDescription;
		}

		return null;
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

	public function getThrowType(): ?Type
	{
		return $this->throwType;
	}

}
