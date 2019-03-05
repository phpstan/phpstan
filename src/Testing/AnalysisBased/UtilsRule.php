<?php declare(strict_types = 1);

namespace PHPStan\Testing\AnalysisBased;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use function implode;
use function is_string;
use function sprintf;

class UtilsRule implements \PHPStan\Rules\Rule
{

	/** @var mixed[][] */
	private $expectedErrors = [];

	public function getNodeType(): string
	{
		return Node\Expr\StaticCall::class;
	}

	/**
	 * @return mixed[][]
	 */
	public function getExpectedErrors(): array
	{
		return $this->expectedErrors;
	}

	/**
	 * @return string[] errors
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node instanceof Node\Expr\StaticCall) {
			return [];
		}

		$nodeClass = $node->class;
		if (!$nodeClass instanceof Node\Name) {
			return [];
		}

		if ($nodeClass->toString() !== Utils::class) {
			return [];
		}

		$nodeName = $node->name;
		if (!$nodeName instanceof Node\Identifier) {
			return [];
		}

		$nodeName = $nodeName->toString();
		if ($nodeName === 'assertTypeDescription') {
			$valueType = $scope->getType($node->args[0]->value);
			$descriptionType = $scope->getType($node->args[1]->value);
			if (!$descriptionType instanceof ConstantStringType) {
				return ['Description must be constant string'];
			}

			$valueDescription = $this->getDescription($valueType);
			if ($valueDescription === $descriptionType->getValue()) {
				return [];
			}

			return [sprintf('Expected type is %s, current type is %s', $descriptionType->getValue(), $valueDescription)];
		}

		if ($nodeName === 'assertExistence') {
			$errors = [];

			$value = $node->args[0]->value;
			if (!$value instanceof Variable) {
				return ['Value must be variable'];
			}

			$variableName = $value->name;
			if (!is_string($variableName)) {
				return ['Value must be variable'];
			}

			if (!$scope->hasVariableType($variableName)->yes()) {
				$errors[] = sprintf('Variable $%s is expected to be exists', $variableName);
			}

			if (isset($node->args[1])) {
				$descriptionType = $scope->getType($node->args[1]->value);
				if (!$descriptionType instanceof ConstantStringType) {
					return ['Description must be constant string'];
				}

				$valueDescription = $this->getDescription($scope->getVariableType($variableName));
				if ($valueDescription === $descriptionType->getValue()) {
					return [];
				}

				$errors[] = sprintf('Expected type is %s, current type is %s', $descriptionType->getValue(), $valueDescription);
			}

			return $errors;
		}

		if ($nodeName === 'assertNoExistence') {
			$value = $node->args[0]->value;
			if (!$value instanceof Variable) {
				return ['Value must be variable'];
			}

			$variableName = $value->name;
			if (!is_string($variableName)) {
				return ['Value must be variable'];
			}

			if ($scope->hasVariableType($variableName)->no()) {
				return [];
			}

			return [sprintf('Variable $%s is not expected to be exists', $variableName)];
		}

		if ($nodeName === 'assertMaybeExistence') {
			$errors = [];

			$value = $node->args[0]->value;
			if (!$value instanceof Variable) {
				return ['Value must be variable'];
			}

			$variableName = $value->name;
			if (!is_string($variableName)) {
				return ['Value must be variable'];
			}

			if (!$scope->hasVariableType($variableName)->maybe()) {
				$errors[] = sprintf('Variable $%s is expected to be maybe exists', $variableName);
			}

			if (isset($node->args[1])) {
				$descriptionType = $scope->getType($node->args[1]->value);
				if (!$descriptionType instanceof ConstantStringType) {
					return ['Description must be constant string'];
				}

				$valueDescription = $this->getDescription($scope->getVariableType($variableName));
				if ($valueDescription === $descriptionType->getValue()) {
					return [];
				}

				$errors[] = sprintf('Expected type is %s, current type is %s', $descriptionType->getValue(), $valueDescription);
			}

			return $errors;
		}

		if ($nodeName === 'assertErrorOnNextLine') {
			$descriptionType = $scope->getType($node->args[0]->value);
			if (!$descriptionType instanceof ConstantStringType) {
				return ['Description must be constant string'];
			}

			$this->expectedErrors[] = [$descriptionType->getValue(), $node->getEndLine() + 1];
			return [];
		}

		if ($nodeName === 'printType') {
			$valueType = $scope->getType($node->args[0]->value);
			return [sprintf('Type: %s', $this->getDescription($valueType))];
		}

		if ($nodeName === 'debugScope') {
			$debug = [];
			foreach ($scope->debug() as $name => $type) {
				$debug[] = sprintf('%s: %s', $name, $type);
			}
			$debug = implode("\n", $debug);
			return [$debug];
		}

		throw new \PHPStan\ShouldNotHappenException();
	}

	private function getDescription(Type $type): string
	{
		if ($type instanceof ErrorType) {
			return '*ERROR*';
		}

		return $type->describe(VerbosityLevel::precise());
	}

}
