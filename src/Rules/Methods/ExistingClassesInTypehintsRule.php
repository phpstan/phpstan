<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Reflection\Php\PhpMethodFromParserNodeReflection;
use PHPStan\Rules\FunctionDefinitionCheck;
use PHPStan\Rules\RuleError;

class ExistingClassesInTypehintsRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Rules\FunctionDefinitionCheck */
	private $check;

	public function __construct(FunctionDefinitionCheck $check)
	{
		$this->check = $check;
	}

	public function getNodeType(): string
	{
		return InClassMethodNode::class;
	}

	/**
	 * @param \PHPStan\Node\InClassMethodNode $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return RuleError[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$methodReflection = $scope->getFunction();
		if (!$methodReflection instanceof PhpMethodFromParserNodeReflection) {
			throw new \PHPStan\ShouldNotHappenException();
		}
		if (!$scope->isInClass()) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		return $this->check->checkClassMethod(
			$methodReflection,
			$node->getOriginalNode(),
			sprintf(
				'Parameter $%%s of method %s::%s() has invalid typehint type %%s.',
				$scope->getClassReflection()->getDisplayName(),
				$methodReflection->getName()
			),
			sprintf(
				'Return typehint of method %s::%s() has invalid type %%s.',
				$scope->getClassReflection()->getDisplayName(),
				$methodReflection->getName()
			)
		);
	}

}
