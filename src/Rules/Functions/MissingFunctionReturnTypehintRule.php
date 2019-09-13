<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\MixedType;

final class MissingFunctionReturnTypehintRule implements \PHPStan\Rules\Rule
{

	/** @var Broker */
	private $broker;

	public function __construct(Broker $broker)
	{
		$this->broker = $broker;
	}

	public function getNodeType(): string
	{
		return \PhpParser\Node\Stmt\Function_::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\Function_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 *
	 * @return string[] errors
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$functionName = $node->name->name;
		if (isset($node->namespacedName)) {
			$functionName = (string) $node->namespacedName;
		}
		$functionNameName = new Name($functionName);
		if (!$this->broker->hasCustomFunction($functionNameName, null)) {
			return [];
		}
		$functionReflection = $this->broker->getCustomFunction($functionNameName, null);
		$returnType = ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();

		if ($returnType instanceof MixedType && !$returnType->isExplicitMixed()) {
			return [
				sprintf(
					'Function %s() has no return typehint specified.',
					$functionReflection->getName()
				),
			];
		}

		return [];
	}

}
