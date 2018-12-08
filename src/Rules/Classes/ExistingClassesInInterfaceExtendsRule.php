<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\ClassNameNodePair;
use PHPStan\Rules\RuleError;

class ExistingClassesInInterfaceExtendsRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Rules\ClassCaseSensitivityCheck */
	private $classCaseSensitivityCheck;

	public function __construct(ClassCaseSensitivityCheck $classCaseSensitivityCheck)
	{
		$this->classCaseSensitivityCheck = $classCaseSensitivityCheck;
	}

	public function getNodeType(): string
	{
		return Node\Stmt\Interface_::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\Interface_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return RuleError[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		return $this->classCaseSensitivityCheck->checkClassNames(
			array_map(static function (Node\Name $interfaceName): ClassNameNodePair {
				return new ClassNameNodePair((string) $interfaceName, $interfaceName);
			}, $node->extends)
		);
	}

}
