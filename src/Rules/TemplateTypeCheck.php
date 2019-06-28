<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PhpParser\Node\FunctionLike;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Type\ErrorType;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\Generic\TemplateTypeFactory;
use PHPStan\Type\Generic\TemplateTypeScope;
use PHPStan\Type\VerbosityLevel;

class TemplateTypeCheck
{

	/** @var \PHPStan\Type\FileTypeMapper */
	private $fileTypeMapper;

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Rules\ClassCaseSensitivityCheck */
	private $classCaseSensitivityCheck;

	/** @var bool */
	private $checkClassCaseSensitivity;

	public function __construct(
		FileTypeMapper $fileTypeMapper,
		Broker $broker,
		ClassCaseSensitivityCheck $classCaseSensitivityCheck,
		bool $checkClassCaseSensitivity
	)
	{
		$this->fileTypeMapper = $fileTypeMapper;
		$this->broker = $broker;
		$this->classCaseSensitivityCheck = $classCaseSensitivityCheck;
		$this->checkClassCaseSensitivity = $checkClassCaseSensitivity;
	}

	/**
	 * @param \PhpParser\Node\FunctionLike $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param \PHPStan\Type\Generic\TemplateTypeScope $templateTypeScope
	 * @param string $boundMessage
	 * @param string $nonexistentClassMessage
	 * @return \PHPStan\Rules\RuleError[]
	 */
	public function checkTemplateTypeDeclarations(
		FunctionLike $node,
		Scope $scope,
		TemplateTypeScope $templateTypeScope,
		string $boundMessage,
		string $nonexistentClassMessage
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
			if ($templateType instanceof ErrorType) {
				$errors[] = RuleErrorBuilder::message(sprintf(
					$boundMessage,
					$tag->getName(),
					$tag->getBound()->describe(VerbosityLevel::precise())
				))->build();
			}

			$referencedClasses = $templateType->getReferencedClasses();
			foreach ($referencedClasses as $referencedClass) {
				if ($this->broker->hasClass($referencedClass)) {
					if ($this->broker->getClass($referencedClass)->isTrait()) {
						$errors[] = RuleErrorBuilder::message(sprintf(
							$nonexistentClassMessage,
							$tag->getName(),
							$tag->getBound()->describe(VerbosityLevel::precise())
						))->build();
					}
					continue;
				}

				$errors[] = RuleErrorBuilder::message(sprintf(
					$nonexistentClassMessage,
					$tag->getName(),
					$tag->getBound()->describe(VerbosityLevel::precise())
				))->build();
			}

			if (!$this->checkClassCaseSensitivity) {
				continue;
			}

			$errors = array_merge(
				$errors,
				$this->classCaseSensitivityCheck->checkClassNames(array_map(static function (string $class) use ($node): ClassNameNodePair {
					return new ClassNameNodePair($class, $node);
				}, $referencedClasses))
			);
		}

		return $errors;
	}

}
