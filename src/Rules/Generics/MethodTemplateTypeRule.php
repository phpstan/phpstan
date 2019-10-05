<?php declare(strict_types = 1);

namespace PHPStan\Rules\Generics;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Type\FileTypeMapper;

class MethodTemplateTypeRule implements Rule
{

	/** @var \PHPStan\Type\FileTypeMapper */
	private $fileTypeMapper;

	/** @var \PHPStan\Rules\Generics\TemplateTypeCheck */
	private $templateTypeCheck;

	public function __construct(
		FileTypeMapper $fileTypeMapper,
		TemplateTypeCheck $templateTypeCheck
	)
	{
		$this->fileTypeMapper = $fileTypeMapper;
		$this->templateTypeCheck = $templateTypeCheck;
	}

	public function getNodeType(): string
	{
		return Node\Stmt\ClassMethod::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\Interface_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return \PHPStan\Rules\RuleError[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$docComment = $node->getDocComment();
		if ($docComment === null) {
			return [];
		}

		if (!$scope->isInClass()) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$className = $scope->getClassReflection()->getDisplayName();
		$methodName = $node->name->toString();
		$resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
			$scope->getFile(),
			$className,
			$scope->isInTrait() ? $scope->getTraitReflection()->getName() : null,
			$docComment->getText()
		);

		return $this->templateTypeCheck->check(
			$resolvedPhpDoc->getTemplateTags(),
			sprintf('PHPDoc tag @template for method %s::%s() cannot have existing class %%s as its name.', $className, $methodName),
			sprintf('PHPDoc tag @template %%s for method %s::%s() has invalid bound type %%s.', $className, $methodName)
		);
	}

}
