<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Type;
use PHPStan\Type\TypehintHelper;

class PhpMethodFromParserNodeReflection implements MethodReflection
{

	/** @var \PHPStan\Reflection\ClassReflection */
	private $declaringClass;

	/** @var \PhpParser\Node\Stmt\ClassMethod */
	private $classMethod;

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

	public function __construct(
		ClassReflection $declaringClass,
		ClassMethod $classMethod,
		array $realParameterTypes,
		array $phpDocParameterTypes,
		bool $realReturnTypePresent,
		Type $realReturnType,
		Type $phpDocReturnType = null,
		bool $isVariadic
	)
	{
		$this->declaringClass = $declaringClass;
		$this->classMethod = $classMethod;
		$this->realParameterTypes = $realParameterTypes;
		$this->phpDocParameterTypes = $phpDocParameterTypes;
		$this->realReturnTypePresent = $realReturnTypePresent;
		$this->realReturnType = $realReturnType;
		$this->phpDocReturnType = $phpDocReturnType;
		$this->isVariadic = $isVariadic;
	}

	public function getDeclaringClass(): ClassReflection
	{
		return $this->declaringClass;
	}

	public function isStatic(): bool
	{
		return $this->classMethod->isStatic();
	}

	public function isPrivate(): bool
	{
		return $this->classMethod->isPrivate();
	}

	public function isPublic(): bool
	{
		return $this->classMethod->isPublic();
	}

	public function getName(): string
	{
		return $this->classMethod->name;
	}

	/**
	 * @return \PHPStan\Reflection\ParameterReflection[]
	 */
	public function getParameters(): array
	{
		if ($this->parameters === null) {
			$parameters = [];
			$isOptional = true;
			foreach (array_reverse($this->classMethod->params) as $parameter) {
				if (!$isOptional || $parameter->default === null) {
					$isOptional = false;
				}

				$parameters[] = new PhpParameterFromParserNodeReflection(
					$parameter->name,
					$isOptional,
					$this->realParameterTypes[$parameter->name],
					isset($this->phpDocParameterTypes[$parameter->name]) ? $this->phpDocParameterTypes[$parameter->name] : null,
					$parameter->byRef,
					$parameter->default
				);
			}

			$this->parameters = array_reverse($parameters);
		}

		return $this->parameters;
	}

	public function isVariadic(): bool
	{
		return $this->isVariadic;
	}

	public function getReturnType(): Type
	{
		if ($this->returnType === null) {
			$phpDocReturnType = $this->phpDocReturnType;
			if (
				$this->realReturnTypePresent
				&& $phpDocReturnType !== null
				&& $this->realReturnType->isNullable() !== $phpDocReturnType->isNullable()
			) {
				$phpDocReturnType = null;
			}
			$this->returnType = TypehintHelper::decideType($this->realReturnType, $phpDocReturnType);
		}

		return $this->returnType;
	}

}
