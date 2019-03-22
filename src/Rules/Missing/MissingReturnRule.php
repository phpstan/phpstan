<?php declare(strict_types = 1);

namespace PHPStan\Rules\Missing;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\ExecutionEndNode;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\MixedType;
use PHPStan\Type\VerbosityLevel;
use PHPStan\Type\VoidType;

class MissingReturnRule implements Rule
{

	/** @var bool */
	private $checkExplicitMixedMissingReturn;

	/** @var bool */
	private $checkPhpDocMissingReturn;

	public function __construct(
		bool $checkExplicitMixedMissingReturn,
		bool $checkPhpDocMissingReturn
	)
	{
		$this->checkExplicitMixedMissingReturn = $checkExplicitMixedMissingReturn;
		$this->checkPhpDocMissingReturn = $checkPhpDocMissingReturn;
	}

	public function getNodeType(): string
	{
		return ExecutionEndNode::class;
	}

	/**
	 * @param ExecutionEndNode $node
	 * @param Scope $scope
	 * @return RuleError[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$statementResult = $node->getStatementResult();
		if ($statementResult->isAlwaysTerminating()) {
			return [];
		}
		if ($statementResult->hasYield()) {
			return [];
		}

		if (!$node->hasNativeReturnTypehint() && !$this->checkPhpDocMissingReturn) {
			return [];
		}

		$anonymousFunctionReturnType = $scope->getAnonymousFunctionReturnType();
		$scopeFunction = $scope->getFunction();
		if ($anonymousFunctionReturnType !== null) {
			$returnType = $anonymousFunctionReturnType;
			$description = 'Anonymous function';
		} elseif ($scopeFunction !== null) {
			$returnType = ParametersAcceptorSelector::selectSingle($scopeFunction->getVariants())->getReturnType();
			if ($scopeFunction instanceof MethodReflection) {
				$description = sprintf('Method %s::%s()', $scopeFunction->getDeclaringClass()->getDisplayName(), $scopeFunction->getName());
			} else {
				$description = sprintf('Function %s()', $scopeFunction->getName());
			}
		} else {
			throw new \PHPStan\ShouldNotHappenException();
		}

		if ($returnType instanceof VoidType) {
			return [];
		}

		if (
			$returnType instanceof MixedType
			&& (
				!$returnType->isExplicitMixed()
				|| !$this->checkExplicitMixedMissingReturn
			)
		) {
			return [];
		}

		return [
			RuleErrorBuilder::message(
				sprintf('%s should return %s but return statement is missing.', $description, $returnType->describe(VerbosityLevel::typeOnly()))
			)->line($node->getNode()->getStartLine())->build(),
		];
	}

}
