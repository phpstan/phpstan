<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ParametersAcceptorWithPhpDocs;
use PHPStan\Reflection\Php\PhpMethodFromParserNodeReflection;
use PHPStan\Type\NonexistentParentClassType;
use PHPStan\Type\VerbosityLevel;

class FunctionDefinitionCheck
{

	private const VALID_TYPEHINTS = [
		'self',
		'array',
		'callable',
		'string',
		'int',
		'bool',
		'float',
		'void',
		'iterable',
		'object',
		'parent',
	];

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Rules\ClassCaseSensitivityCheck */
	private $classCaseSensitivityCheck;

	/** @var bool */
	private $checkClassCaseSensitivity;

	/** @var bool */
	private $checkThisOnly;

	public function __construct(
		Broker $broker,
		ClassCaseSensitivityCheck $classCaseSensitivityCheck,
		bool $checkClassCaseSensitivity,
		bool $checkThisOnly
	)
	{
		$this->broker = $broker;
		$this->classCaseSensitivityCheck = $classCaseSensitivityCheck;
		$this->checkClassCaseSensitivity = $checkClassCaseSensitivity;
		$this->checkThisOnly = $checkThisOnly;
	}

	/**
	 * @param \PhpParser\Node\FunctionLike $function
	 * @param string $parameterMessage
	 * @param string $returnMessage
	 * @return RuleError[]
	 */
	public function checkFunction(
		FunctionLike $function,
		string $parameterMessage,
		string $returnMessage
	): array
	{
		if ($function instanceof ClassMethod) {
			throw new \PHPStan\ShouldNotHappenException('Use FunctionDefinitionCheck::checkClassMethod() instead.');
		}
		if ($function instanceof Function_) {
			$functionName = $function->name->name;
			if (isset($function->namespacedName)) {
				$functionName = (string) $function->namespacedName;
			}
			$functionNameName = new Name($functionName);
			if (!$this->broker->hasCustomFunction($functionNameName, null)) {
				return [];
			}

			$functionReflection = $this->broker->getCustomFunction($functionNameName, null);

			/** @var \PHPStan\Reflection\ParametersAcceptorWithPhpDocs $parametersAcceptor */
			$parametersAcceptor = ParametersAcceptorSelector::selectSingle($functionReflection->getVariants());

			return $this->checkParametersAcceptor(
				$parametersAcceptor,
				$function,
				$parameterMessage,
				$returnMessage
			);
		}

		return $this->checkAnonymousFunction(
			$function->getParams(),
			$function->getReturnType(),
			$parameterMessage,
			$returnMessage
		);
	}

	/**
	 * @param \PhpParser\Node\Param[] $parameters
	 * @param \PhpParser\Node\Identifier|\PhpParser\Node\Name|\PhpParser\Node\NullableType|null $returnTypeNode
	 * @param string $parameterMessage
	 * @param string $returnMessage
	 * @return \PHPStan\Rules\RuleError[]
	 */
	public function checkAnonymousFunction(
		array $parameters,
		$returnTypeNode,
		string $parameterMessage,
		string $returnMessage
	): array
	{
		$errors = [];
		foreach ($parameters as $param) {
			if ($param->type === null) {
				continue;
			}
			$class = $param->type instanceof NullableType
				? (string) $param->type->type
				: (string) $param->type;
			$lowercasedClass = strtolower($class);
			if ($lowercasedClass === '' || in_array($lowercasedClass, self::VALID_TYPEHINTS, true)) {
				continue;
			}

			if (!$this->broker->hasClass($class) || $this->broker->getClass($class)->isTrait()) {
				if (!$param->var instanceof Variable || !is_string($param->var->name)) {
					throw new \PHPStan\ShouldNotHappenException();
				}
				$errors[] = RuleErrorBuilder::message(sprintf($parameterMessage, $param->var->name, $class))->line($param->type->getLine())->build();
			} elseif ($this->checkClassCaseSensitivity) {
				$errors = array_merge(
					$errors,
					$this->classCaseSensitivityCheck->checkClassNames([
						new ClassNameNodePair($class, $param->type),
					])
				);
			}
		}

		if ($returnTypeNode === null) {
			return $errors;
		}

		$returnType = $returnTypeNode instanceof NullableType
			? (string) $returnTypeNode->type
			: (string) $returnTypeNode;

		$lowercasedReturnType = strtolower($returnType);

		if (
			$lowercasedReturnType !== ''
			&& !in_array($lowercasedReturnType, self::VALID_TYPEHINTS, true)
		) {
			if (!$this->broker->hasClass($returnType) || $this->broker->getClass($returnType)->isTrait()) {
				$errors[] = RuleErrorBuilder::message(sprintf($returnMessage, $returnType))->line($returnTypeNode->getLine())->build();
			} elseif ($this->checkClassCaseSensitivity) {
				$errors = array_merge(
					$errors,
					$this->classCaseSensitivityCheck->checkClassNames([
						new ClassNameNodePair($returnType, $returnTypeNode),
					])
				);
			}
		}

		return $errors;
	}

	/**
	 * @param PhpMethodFromParserNodeReflection $methodReflection
	 * @param ClassMethod $methodNode
	 * @param string $parameterMessage
	 * @param string $returnMessage
	 * @return RuleError[]
	 */
	public function checkClassMethod(
		PhpMethodFromParserNodeReflection $methodReflection,
		ClassMethod $methodNode,
		string $parameterMessage,
		string $returnMessage
	): array
	{
		/** @var \PHPStan\Reflection\ParametersAcceptorWithPhpDocs $parametersAcceptor */
		$parametersAcceptor = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());

		return $this->checkParametersAcceptor(
			$parametersAcceptor,
			$methodNode,
			$parameterMessage,
			$returnMessage
		);
	}

	/**
	 * @param ParametersAcceptorWithPhpDocs $parametersAcceptor
	 * @param FunctionLike $functionNode
	 * @param string $parameterMessage
	 * @param string $returnMessage
	 * @return RuleError[]
	 */
	private function checkParametersAcceptor(
		ParametersAcceptorWithPhpDocs $parametersAcceptor,
		FunctionLike $functionNode,
		string $parameterMessage,
		string $returnMessage
	): array
	{
		$errors = [];
		$parameterNodes = $functionNode->getParams();
		$returnTypeNode = $functionNode->getReturnType() ?? $functionNode;
		foreach ($parametersAcceptor->getParameters() as $parameter) {
			if ($this->checkThisOnly) {
				$referencedClasses = $parameter->getType()->getReferencedClasses();
			} else {
				$referencedClasses = array_merge(
					$parameter->getNativeType()->getReferencedClasses(),
					$parameter->getPhpDocType()->getReferencedClasses()
				);
			}
			$parameterNode = null;
			$parameterNodeCallback = function () use ($parameter, $parameterNodes, &$parameterNode): Param {
				if ($parameterNode === null) {
					$parameterNode = $this->getParameterNode($parameter->getName(), $parameterNodes);
				}

				return $parameterNode;
			};
			foreach ($referencedClasses as $class) {
				if ($this->broker->hasClass($class) && !$this->broker->getClass($class)->isTrait()) {
					continue;
				}

				$errors[] = RuleErrorBuilder::message(sprintf(
					$parameterMessage,
					$parameter->getName(),
					$class
				))->line($parameterNodeCallback()->getLine())->build();
			}

			if ($this->checkClassCaseSensitivity) {
				$errors = array_merge(
					$errors,
					$this->classCaseSensitivityCheck->checkClassNames(array_map(static function (string $class) use ($parameterNodeCallback): ClassNameNodePair {
						return new ClassNameNodePair($class, $parameterNodeCallback());
					}, $referencedClasses))
				);
			}
			if (!($parameter->getType() instanceof NonexistentParentClassType)) {
				continue;
			}

			$errors[] = RuleErrorBuilder::message(sprintf($parameterMessage, $parameter->getName(), $parameter->getType()->describe(VerbosityLevel::typeOnly())))->line($parameterNodeCallback()->getLine())->build();
		}

		if ($this->checkThisOnly) {
			$returnTypeReferencedClasses = $parametersAcceptor->getReturnType()->getReferencedClasses();
		} else {
			$returnTypeReferencedClasses = array_merge(
				$parametersAcceptor->getNativeReturnType()->getReferencedClasses(),
				$parametersAcceptor->getPhpDocReturnType()->getReferencedClasses()
			);
		}

		foreach ($returnTypeReferencedClasses as $class) {
			if ($this->broker->hasClass($class) && !$this->broker->getClass($class)->isTrait()) {
				continue;
			}

			$errors[] = RuleErrorBuilder::message(sprintf($returnMessage, $class))->line($returnTypeNode->getLine())->build();
		}

		if ($this->checkClassCaseSensitivity) {
			$errors = array_merge(
				$errors,
				$this->classCaseSensitivityCheck->checkClassNames(array_map(static function (string $class) use ($returnTypeNode): ClassNameNodePair {
					return new ClassNameNodePair($class, $returnTypeNode);
				}, $returnTypeReferencedClasses))
			);
		}
		if ($parametersAcceptor->getReturnType() instanceof NonexistentParentClassType) {
			$errors[] = RuleErrorBuilder::message(sprintf($returnMessage, $parametersAcceptor->getReturnType()->describe(VerbosityLevel::typeOnly())))->line($returnTypeNode->getLine())->build();
		}

		return $errors;
	}

	/**
	 * @param string $parameterName
	 * @param Param[] $parameterNodes
	 * @return Param
	 */
	private function getParameterNode(
		string $parameterName,
		array $parameterNodes
	): Param
	{
		foreach ($parameterNodes as $param) {
			if ($param->var instanceof \PhpParser\Node\Expr\Error) {
				continue;
			}

			if (!is_string($param->var->name)) {
				continue;
			}

			if ($param->var->name === $parameterName) {
				return $param;
			}
		}

		throw new \PHPStan\ShouldNotHappenException(sprintf('Parameter %s not found.', $parameterName));
	}

}
