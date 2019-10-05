<?php declare(strict_types = 1);

namespace PHPStan\Rules\Generics;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\Generic\TemplateTypeScope;

class TraitTemplateTypeRule implements Rule
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
		return Node\Stmt\Trait_::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\Trait_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return \PHPStan\Rules\RuleError[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$docComment = $node->getDocComment();
		if ($docComment === null) {
			return [];
		}

		if (!isset($node->namespacedName)) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$traitName = (string) $node->namespacedName;
		$resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
			$scope->getFile(),
			$traitName,
			null,
			$docComment->getText()
		);

		return $this->templateTypeCheck->check(
			TemplateTypeScope::createWithClass($traitName),
			$resolvedPhpDoc->getTemplateTags(),
			sprintf('PHPDoc tag @template for trait %s cannot have existing class %%s as its name.', $traitName),
			sprintf('PHPDoc tag @template %%s for trait %s has invalid bound type %%s.', $traitName),
			sprintf('PHPDoc tag @template %%s for trait %s with bound type %%s is not supported.', $traitName)
		);
	}

}
