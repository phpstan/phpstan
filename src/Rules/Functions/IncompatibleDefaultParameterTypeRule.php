<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Rules\Rule;
use PHPStan\Type\VerbosityLevel;

class IncompatibleDefaultParameterTypeRule implements Rule
{

	/** @var Broker */
	private $broker;

	public function __construct(Broker $broker)
	{
		$this->broker = $broker;
	}

	public function getNodeType(): string
	{
		return FunctionLike::class;
	}

	/**
	 * @param FunctionLike $node
	 * @param Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if ($node instanceof Function_) {
			$type = 'function';
			$name = $node->namespacedName;
			if (!$this->broker->hasFunction($name, $scope)) {
				return [];
			}

			$function = $this->broker->getFunction($name, $scope);
			$parameters = ParametersAcceptorSelector::selectSingle($function->getVariants());
			$nameToPrint = $function->getName();
		} elseif ($node instanceof ClassMethod) {
			$type = 'method';
			$name = (string) $node->name;

			$class = $scope->getClassReflection();
			if ($class === null) {
				throw new \PHPStan\ShouldNotHappenException();
			}

			$method = $class->getNativeMethod($name);
			$parameters = ParametersAcceptorSelector::selectSingle($method->getVariants());
			$nameToPrint = sprintf('%s::%s', $method->getDeclaringClass()->getName(), $method->getName());
		} else {
			return [];
		}

		$errors = [];
		foreach ($node->getParams() as $paramI => $param) {
			if ($param->default === null) {
				continue;
			}
			if (
				$param->var instanceof Node\Expr\Error
				|| !is_string($param->var->name)
			) {
				throw new \PHPStan\ShouldNotHappenException();
			}

			$defaultValueType = $scope->getType($param->default);
			$parameterType = $parameters->getParameters()[$paramI]->getType();

			if ($parameterType->isSuperTypeOf($defaultValueType)->yes()) {
				continue;
			}

			$errors[] = sprintf(
				'Default value of the parameter #%d $%s (%s) of %s %s() is incompatible with type %s.',
				$paramI + 1,
				$param->var->name,
				$defaultValueType->describe(VerbosityLevel::value()),
				$type,
				$nameToPrint,
				$parameterType->describe(VerbosityLevel::value())
			);
		}

		return $errors;
	}

}
