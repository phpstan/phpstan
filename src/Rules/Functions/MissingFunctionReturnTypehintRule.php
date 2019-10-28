<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Rules\MissingTypehintCheck;
use PHPStan\Type\MixedType;
use PHPStan\Type\VerbosityLevel;

final class MissingFunctionReturnTypehintRule implements \PHPStan\Rules\Rule
{

	/** @var Broker */
	private $broker;

	/** @var \PHPStan\Rules\MissingTypehintCheck */
	private $missingTypehintCheck;

	public function __construct(
		Broker $broker,
		MissingTypehintCheck $missingTypehintCheck
	)
	{
		$this->broker = $broker;
		$this->missingTypehintCheck = $missingTypehintCheck;
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

		$messages = [];
		foreach ($this->missingTypehintCheck->getIterableTypesWithMissingValueTypehint($returnType) as $iterableType) {
			$messages[] = sprintf('Function %s() return type has no value type specified in iterable type %s.', $functionReflection->getName(), $iterableType->describe(VerbosityLevel::typeOnly()));
		}

		return $messages;
	}

}
