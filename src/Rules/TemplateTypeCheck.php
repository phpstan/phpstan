<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PhpParser\Node\FunctionLike;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ErrorType;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\Generic\TemplateTypeFactory;
use PHPStan\Type\Generic\TemplateTypeScope;
use PHPStan\Type\VerbosityLevel;

class TemplateTypeCheck
{

	/** @var \PHPStan\Type\FileTypeMapper */
	private $fileTypeMapper;

	public function __construct(FileTypeMapper $fileTypeMapper)
	{
		$this->fileTypeMapper = $fileTypeMapper;
	}

	/** @return string[] */
	public function checkTemplateTypeDeclarations(
		FunctionLike $node,
		Scope $scope,
		TemplateTypeScope $templateTypeScope,
		string $boundMessage
	): array
	{
		$docComment = $node->getDocComment();
		if ($docComment === null) {
			return [];
		}

		$resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
			$scope->getFile(),
			$scope->isInClass() ? $scope->getClassReflection()->getName() : null,
			$scope->isInTrait() ? $scope->getTraitReflection()->getName() : null,
			$docComment->getText()
		);

		$errors = [];

		foreach ($resolvedPhpDoc->getTemplateTags() as $tag) {
			$templateType = TemplateTypeFactory::fromTemplateTag($templateTypeScope, $tag);
			if (!($templateType instanceof ErrorType)) {
				continue;
			}

			$errors[] = sprintf(
				$boundMessage,
				$tag->getName(),
				$tag->getBound()->describe(VerbosityLevel::precise())
			);
		}

		return $errors;
	}

}
