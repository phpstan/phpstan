<?php declare(strict_types=1);

namespace PHPStan\Rules\PhpDoc;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MissingMethodFromReflectionException;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

class IncompatiblePhpDocInheritedReturnType implements \PHPStan\Rules\Rule
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
	 * @param Node|Node\Stmt\ClassMethod $node
	 * @param \PHPStan\Analyser\Scope    $scope
	 *
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$parents = $scope->getClassReflection()->getParents();

		if (!count($parents)) {
			return [];
		}

		$docComment = $node->getDocComment();
		if ($docComment === null) {
			return [];
		}

		$resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
			$scope->getFile(),
			$scope->getClassReflection()->getName(),
			$docComment->getText()
		);

		try {
			/** @var PhpMethodReflection $parentMethod */
			$parentMethod = $parents[0]->getMethod($node->name, $scope);
		} catch (MissingMethodFromReflectionException $e) {
			return [];
		}

		$resolvedParentPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
			$scope->getFile(),
			$parents[0]->getName(),
			$parentMethod->getDocComment()
		);

		$errors = [];

		if ($resolvedPhpDoc->getReturnTag() !== null) {
			$phpDocReturnType = $resolvedPhpDoc->getReturnTag()->getType();
			$phpDocParentReturnType = $resolvedParentPhpDoc->getReturnTag()->getType();

			if (!($phpDocReturnType instanceof UnionType) && $phpDocReturnType !== $phpDocParentReturnType) {
				$errors[] = sprintf(
					'PHPDoc return type %s does contain inherited return type %s. Expected compound %s.',
					$this->formatType($phpDocParentReturnType),
					$this->formatType($phpDocReturnType),
					$this->formatType($phpDocReturnType, $phpDocParentReturnType)
				);
			}

			if ($phpDocReturnType instanceof UnionType) {

				if (!in_array($phpDocParentReturnType, $phpDocReturnType->getTypes())) {
					$errors[] = sprintf(
						'PHPDoc return type compound %s does contain inherited return type %s.',
						$this->formatType(...$phpDocReturnType->getTypes()),
						$this->formatType($phpDocParentReturnType)
					);
				}

			}

		}

		return $errors;
	}

	/**
	 * @param Type[] ...$types
	 *
	 * @return string
	 */
	private function formatType(...$types)
	{
		$string = '|';

		foreach ($types as $type) {

			if ($type instanceof UnionType) {
				$string = implode('|', array_map(function (Type $type) {
					return $type->describe();
				}, $type->getTypes())) . '|';

				continue;
			}

			$string .= $type->describe() . '|';

		}

		return trim($string, '|');
	}
}
