<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PhpParser\Node;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\TemplateTypeCheck;
use PHPStan\Type\Generic\TemplateTypeScope;

class TemplateTypeDeclarationRule implements \PHPStan\Rules\Rule
{

	/** @var TemplateTypeCheck */
	private $templateTypeCheck;

	public function __construct(TemplateTypeCheck $check)
	{
		$this->templateTypeCheck = $check;
	}

	public function getNodeType(): string
	{
		return Function_::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\Function_ $node
	 * @return \PHPStan\Rules\RuleError[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$functionName = (string) $node->name;
		if (isset($node->namespacedName)) {
			$functionName = (string) $node->namespacedName;
		}

		$templateTypeScope = TemplateTypeScope::createWithFunction($functionName);

		return $this->templateTypeCheck->checkTemplateTypeDeclarations(
			$node,
			$scope,
			$templateTypeScope,
			sprintf(
				'Type parameter %%s of function %s() has invalid bound %%s (only class name bounds are supported currently).',
				$functionName
			),
			sprintf(
				'Type parameter %%s of function %s() has unknown class %%s as its bound.',
				$functionName
			)
		);
	}

}
