<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Reflection\Php\PhpMethodFromParserNodeReflection;
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
		return InClassMethodNode::class;
	}

	/**
	 * @param \PHPStan\Node\InClassMethodNode $node
	 * @return string[]
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

		$templateTypeScope = TemplateTypeScope::createWithMethod(
			$methodReflection->getDeclaringClass()->getName(),
			$methodReflection->getName()
		);

		return $this->templateTypeCheck->checkTemplateTypeDeclarations(
			$node->getOriginalNode(),
			$scope,
			$templateTypeScope,
			sprintf(
				'Type parameter %%s of method %s::%s() has invalid bound %%s (only class name bounds are supported currently).',
				$scope->getClassReflection()->getDisplayName(),
				$methodReflection->getName()
			)
		);
	}

}
