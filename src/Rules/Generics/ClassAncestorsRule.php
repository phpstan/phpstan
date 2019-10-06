<?php declare(strict_types = 1);

namespace PHPStan\Rules\Generics;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\PhpDoc\Tag\ExtendsTag;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Type;
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

		return $this->checkExtends($className, $node->extends !== null ? [$node->extends] : [], array_map(static function (ExtendsTag $tag): Type {
			return $tag->getType();
		}, $resolvedPhpDoc->getExtendsTags()));
	}

	/**
	 * @param string $className
	 * @param array<int, \PhpParser\Node\Name> $nameNodes
	 * @param array<\PHPStan\Type\Type> $ancestorTypes
	 * @return \PHPStan\Rules\RuleError[]
	 */
	private function checkExtends(string $className, array $nameNodes, array $ancestorTypes): array
	{
		if (count($ancestorTypes) === 0) {
			return [];
		}

		$names = array_fill_keys(array_map(static function (Node\Name $nameNode): string {
			return $nameNode->toString();
		}, $nameNodes), true);

		$messages = [];
		foreach ($ancestorTypes as $ancestorType) {
			if (!$ancestorType instanceof GenericObjectType) {
				$messages[] = RuleErrorBuilder::message(sprintf('Class %s @extends tag contains incompatible type %s.', $className, $ancestorType->describe(VerbosityLevel::typeOnly())))->build();
				continue;
			}

			$ancestorTypeClassName = $ancestorType->getClassName();
			if (!isset($names[$ancestorTypeClassName])) {
				if (count($names) === 0) {
					$messages[] = RuleErrorBuilder::message(sprintf('Class %s has @extends tag, but does not extend any class.', $className))->build();
				} else {
					$messages[] = RuleErrorBuilder::message(sprintf('The @extends tag of class %s describes %s but the class extends %s.', $className, $ancestorTypeClassName, implode(',', array_keys($names))))->build();
				}

				continue;
			}

			$genericObjectTypeCheckMessages = $this->genericObjectTypeCheck->check(
				$ancestorType,
				'PHPDoc tag @extends contains generic type %s but class %s is not generic.',
				'Generic type %s in PHPDoc tag @extends does not specify all template types of class %s: %s',
				'Generic type %s in PHPDoc tag @extends specifies %d template types, but class %s supports only %d: %s',
				'Type %s in generic type %s in PHPDoc tag @extends is not subtype of template type %s of class %s.'
			);
			$messages = array_merge($messages, $genericObjectTypeCheckMessages);

			foreach ($ancestorType->getReferencedClasses() as $referencedClass) {
				if (
					$this->broker->hasClass($referencedClass)
					&& !$this->broker->getClass($referencedClass)->isTrait()
				) {
					continue;
				}

				$messages[] = RuleErrorBuilder::message(sprintf('PHPDoc tag @extends has invalid type %s.', $referencedClass))->build();
			}
		}

		return $messages;
	}

}
