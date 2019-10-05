<?php declare(strict_types = 1);

namespace PHPStan\Rules\Generics;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\FileTypeMapper;

class ClassTemplateTypeRule implements Rule
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Type\FileTypeMapper */
	private $fileTypeMapper;

	public function __construct(
		Broker $broker,
		FileTypeMapper $fileTypeMapper
	)
	{
		$this->broker = $broker;
		$this->fileTypeMapper = $fileTypeMapper;
	}

	public function getNodeType(): string
	{
		return Node\Stmt\Class_::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\ClassLike $node
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

		$className = (string) $node->namespacedName;
		$resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
			$scope->getFile(),
			$className,
			null,
			$docComment->getText()
		);

		$messages = [];
		foreach ($resolvedPhpDoc->getTemplateTags() as $templateTag) {
			$templateTagName = $templateTag->getName();
			if ($this->broker->hasClass($templateTagName)) {
				$messages[] = RuleErrorBuilder::message(sprintf(
					'PHPDoc tag @template for class %s cannot have existing class %s as its name.',
					$className,
					$templateTagName
				))->build();
			}
			$boundType = $templateTag->getBound();
			foreach ($boundType->getReferencedClasses() as $referencedClass) {
				if (
					$this->broker->hasClass($referencedClass)
					&& !$this->broker->getClass($referencedClass)->isTrait()
				) {
					continue;
				}

				$messages[] = RuleErrorBuilder::message(sprintf(
					'PHPDoc tag @template %s for class %s has invalid bound type %s.',
					$templateTagName,
					$className,
					$referencedClass
				))->build();
			}
		}

		return $messages;
	}

}
