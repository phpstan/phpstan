<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\ClassCaseSensitivityCheck;

class ExistingClassInTraitUseRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Rules\ClassCaseSensitivityCheck */
	private $classCaseSensitivityCheck;

	public function __construct(ClassCaseSensitivityCheck $classCaseSensitivityCheck)
	{
		$this->classCaseSensitivityCheck = $classCaseSensitivityCheck;
	}

	public function getNodeType(): string
	{
		return \PhpParser\Node\Stmt\TraitUse::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\TraitUse $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		return $this->classCaseSensitivityCheck->checkClassNames(
			array_map(static function (Node\Name $traitName): string {
				return (string) $traitName;
			}, $node->traits)
		);
	}

}
