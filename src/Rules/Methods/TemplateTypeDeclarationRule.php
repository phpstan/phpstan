<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
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
		return ClassMethod::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\ClassMethod $node
	 * @return \PHPStan\Rules\RuleError[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$classReflection = $scope->getClassReflection();
		if ($classReflection === null) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$templateTypeScope = TemplateTypeScope::createWithMethod(
			$classReflection->getName(),
			$node->name->name
		);

		return $this->templateTypeCheck->checkTemplateTypeDeclarations(
			$node,
			$scope,
			$templateTypeScope,
			sprintf(
				'Type parameter %%s of method %s::%s() has invalid bound %%s (only class name bounds are supported currently).',
				$classReflection->getDisplayName(),
				$node->name->name
			),
			sprintf(
				'Type parameter %%s of method %s::%s() has unknown class %%s as its bound.',
				$classReflection->getDisplayName(),
				$node->name->name
			)
		);
	}

}
