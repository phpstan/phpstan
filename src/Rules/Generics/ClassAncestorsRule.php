<?php declare(strict_types = 1);

namespace PHPStan\Rules\Generics;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\VerbosityLevel;

class ClassAncestorsRule implements Rule
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Type\FileTypeMapper */
	private $fileTypeMapper;

	/** @var \PHPStan\Rules\Generics\GenericObjectTypeCheck */
	private $genericObjectTypeCheck;

	public function __construct(
		Broker $broker,
		FileTypeMapper $fileTypeMapper,
		GenericObjectTypeCheck $genericObjectTypeCheck
	)
	{
		$this->broker = $broker;
		$this->fileTypeMapper = $fileTypeMapper;
		$this->genericObjectTypeCheck = $genericObjectTypeCheck;
	}

	public function getNodeType(): string
	{
		return Node\Stmt\Class_::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\Class_ $node
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
		$extendsTags = $resolvedPhpDoc->getExtendsTags();

		return $this->checkExtends($className, $node->extends, $extendsTags);
	}

	/**
	 * @param string $className
	 * @param \PhpParser\Node\Name|null $classExtends
	 * @param array<string, \PHPStan\PhpDoc\Tag\ExtendsTag> $extendsTags
	 * @return \PHPStan\Rules\RuleError[]
	 */
	private function checkExtends(string $className, ?Node\Name $classExtends, array $extendsTags): array
	{
		if (count($extendsTags) > 1) {
			return [
				RuleErrorBuilder::message(sprintf('Class %s has multiple @extends tags, but can extend only one class.', $className))->build(),
			];
		}

		if (count($extendsTags) === 0) {
			return [];
		}

		if ($classExtends === null) {
			return [
				RuleErrorBuilder::message(sprintf('Class %s has @extends tag, but does not extend any class.', $className))->build(),
			];
		}

		$extendsTagType = array_values($extendsTags)[0]->getType();
		if (!$extendsTagType instanceof GenericObjectType) {
			return [
				RuleErrorBuilder::message(sprintf('Class %s @extends tag contains incompatible type %s.', $className, $extendsTagType->describe(VerbosityLevel::typeOnly())))->build(),
			];
		}

		$extendsClassName = $classExtends->toString();
		if ($extendsTagType->getClassName() !== $extendsClassName) {
			return [
				RuleErrorBuilder::message(sprintf('Class %s extends %s but the @extends tag describes %s.', $className, $extendsClassName, $extendsTagType->getClassName()))->build(),
			];
		}

		$messages = $this->genericObjectTypeCheck->check(
			$extendsTagType,
			'PHPDoc tag @extends contains generic type %s but class %s is not generic.',
			'Generic type %s in PHPDoc tag @extends does not specify all template types of class %s: %s',
			'Generic type %s in PHPDoc tag @extends specifies %d template types, but class %s supports only %d: %s',
			'Type %s in generic type %s in PHPDoc tag @extends is not subtype of template type %s of class %s.'
		);

		foreach ($extendsTagType->getReferencedClasses() as $referencedClass) {
			if (
				$this->broker->hasClass($referencedClass)
				&& !$this->broker->getClass($referencedClass)->isTrait()
			) {
				continue;
			}

			$messages[] = RuleErrorBuilder::message(sprintf('PHPDoc tag @extends has invalid type %s.', $referencedClass))->build();
		}

		return $messages;
	}

}
