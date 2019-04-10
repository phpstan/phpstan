<?php declare(strict_types = 1);

namespace PHPStan\Rules\PhpDoc;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\ObjectType;
use PHPStan\Type\VerbosityLevel;
use PHPStan\Type\VoidType;

class InvalidThrowsPhpDocValueRule implements \PHPStan\Rules\Rule
{

	/** @var FileTypeMapper */
	private $fileTypeMapper;

	public function __construct(FileTypeMapper $fileTypeMapper)
	{
		$this->fileTypeMapper = $fileTypeMapper;
	}

	public function getNodeType(): string
	{
		return \PhpParser\Node\FunctionLike::class;
	}

	/**
	 * @param \PhpParser\Node\FunctionLike $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
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

		if ($resolvedPhpDoc->getThrowsTag() === null) {
			return [];
		}

		$phpDocThrowsType = $resolvedPhpDoc->getThrowsTag()->getType();
		if ((new VoidType())->isSuperTypeOf($phpDocThrowsType)->yes()) {
			return [];
		}

		$isThrowsSuperType = (new ObjectType(\Throwable::class))->isSuperTypeOf($phpDocThrowsType);
		if ($isThrowsSuperType->yes()) {
			return [];
		}

		return [sprintf(
			'PHPDoc tag @throws with type %s is not subtype of Throwable',
			$phpDocThrowsType->describe(VerbosityLevel::typeOnly())
		)];
	}

}
