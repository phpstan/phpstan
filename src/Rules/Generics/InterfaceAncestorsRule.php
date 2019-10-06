<?php declare(strict_types = 1);

namespace PHPStan\Rules\Generics;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDoc\Tag\ExtendsTag;
use PHPStan\PhpDoc\Tag\ImplementsTag;
use PHPStan\Rules\Rule;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\Type;

class InterfaceAncestorsRule implements Rule
{

	/** @var \PHPStan\Type\FileTypeMapper */
	private $fileTypeMapper;

	/** @var \PHPStan\Rules\Generics\GenericAncestorsCheck */
	private $genericAncestorsCheck;

	public function __construct(
		FileTypeMapper $fileTypeMapper,
		GenericAncestorsCheck $genericAncestorsCheck
	)
	{
		$this->fileTypeMapper = $fileTypeMapper;
		$this->genericAncestorsCheck = $genericAncestorsCheck;
	}

	public function getNodeType(): string
	{
		return Node\Stmt\Interface_::class;
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

		if (!isset($node->namespacedName)) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$interfaceName = (string) $node->namespacedName;
		$resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
			$scope->getFile(),
			$interfaceName,
			null,
			$docComment->getText()
		);

		$extendsErrors = $this->genericAncestorsCheck->check(
			$node->extends,
			array_map(static function (ExtendsTag $tag): Type {
				return $tag->getType();
			}, $resolvedPhpDoc->getExtendsTags()),
			sprintf('Interface %s @extends tag contains incompatible type %%s.', $interfaceName),
			sprintf('Interface %s has @extends tag, but does not extend any interface.', $interfaceName),
			sprintf('The @extends tag of interface %s describes %%s but the interface extends: %%s', $interfaceName),
			'PHPDoc tag @extends contains generic type %s but interface %s is not generic.',
			'Generic type %s in PHPDoc tag @extends does not specify all template types of interface %s: %s',
			'Generic type %s in PHPDoc tag @extends specifies %d template types, but interface %s supports only %d: %s',
			'Type %s in generic type %s in PHPDoc tag @extends is not subtype of template type %s of interface %s.',
			'PHPDoc tag @extends has invalid type %s.'
		);

		$implementsErrors = $this->genericAncestorsCheck->check(
			[],
			array_map(static function (ImplementsTag $tag): Type {
				return $tag->getType();
			}, $resolvedPhpDoc->getImplementsTags()),
			sprintf('Interface %s @implements tag contains incompatible type %%s.', $interfaceName),
			sprintf('Interface %s has @implements tag, but can not implement any interface, must extend from it.', $interfaceName),
			'',
			'',
			'',
			'',
			'',
			''
		);

		return array_merge($extendsErrors, $implementsErrors);
	}

}
