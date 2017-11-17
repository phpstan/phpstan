<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\ClassCaseSensitivityCheck;

class ExistingClassInClassExtendsRule implements \PHPStan\Rules\Rule
{

	/**
	 * @var \PHPStan\Rules\ClassCaseSensitivityCheck
	 */
	private $classCaseSensitivityCheck;

	public function __construct(ClassCaseSensitivityCheck $classCaseSensitivityCheck)
	{
		$this->classCaseSensitivityCheck = $classCaseSensitivityCheck;
	}

	public function getNodeType(): string
	{
		return Node\Stmt\Class_::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\Class_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if ($node->extends === null) {
			return [];
		}
		return $this->classCaseSensitivityCheck->checkClassNames([(string) $node->extends]);
	}

}
