<?php declare(strict_types = 1);

namespace PHPStan\Rules\PhpDoc;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionTypeHelper;

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
			// Walk over all parents and merge return types
			$phpDocParentReturnType = $this->mergeParentsReturnType($parents, $node->name, $scope);
		} catch (\PHPStan\Reflection\MissingMethodFromReflectionException $e) {
			return [];
		}

		$errors = [];

		if ($resolvedPhpDoc->getReturnTag() !== null) {
			$phpDocReturnType = $resolvedPhpDoc->getReturnTag()->getType();

			if ($phpDocReturnType->isSuperTypeOf($phpDocParentReturnType)->no()) {
				$errors[] = sprintf(
					'PHPDoc return type %s does contain inherited return type %s. Expected compound %s.',
					$this->formatType($phpDocReturnType),
					$this->formatType($phpDocParentReturnType),
					$this->formatType(...UnionTypeHelper::sortTypes([$phpDocReturnType, $phpDocParentReturnType]))
				);
			}

		}

		return $errors;
	}

	/**
	 * @param Type[] ...$types
	 *
	 * @return string
	 */
	private function formatType(Type ...$types): string
	{
		$string = '';

		foreach ($types as $type) {
			$string .= $type->describe() . '|';
		}

		return trim($string, '|');
	}

	/**
	 * @param ClassReflection[] $parents
	 * @param string            $name
	 * @param Scope             $scope
	 *
	 * @return Type
	 * @throws \PHPStan\Reflection\MissingMethodFromReflectionException
	 */
	private function mergeParentsReturnType(array $parents, string $name, Scope $scope): Type
	{
		$returnTypes = [];

		foreach ($parents as $parent) {

			if (!$parent->hasMethod($name)) {
				continue;
			}

			/** @var PhpMethodReflection $method */
			$method = $parent->getMethod($name, $scope);

			$returnTypes[] = $method->getPhpDocReturnType();
		}

		return TypeCombinator::union(...UnionTypeHelper::sortTypes($returnTypes));
	}

}
