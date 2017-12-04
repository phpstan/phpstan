<?php declare(strict_types = 1);

namespace PHPStan\Rules\PhpDoc;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ArrayType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\Type;

class IncompatiblePhpDocTypeRule implements \PHPStan\Rules\Rule
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
			$docComment->getText()
		);
		$nativeParameterTypes = $this->getNativeParameterTypes($node, $scope);
		$nativeReturnType = $this->getNativeReturnType($node, $scope);

		$errors = [];

		foreach ($resolvedPhpDoc->getParamTags() as $parameterName => $phpDocParamTag) {
			$phpDocParamType = $phpDocParamTag->getType();
			if (!isset($nativeParameterTypes[$parameterName])) {
				$errors[] = sprintf(
					'PHPDoc tag @param references unknown parameter $%s',
					$parameterName
				);

			} elseif ($phpDocParamType instanceof ErrorType) {
				$errors[] = sprintf(
					'PHPDoc tag @param for parameter $%s contains unresolvable type',
					$parameterName
				);

			} else {
				$nativeParamType = $nativeParameterTypes[$parameterName];
				$isParamSuperType = $nativeParamType->isSuperTypeOf($phpDocParamType);

				if (
					$phpDocParamTag->isVariadic()
					&& $nativeParamType instanceof ArrayType
					&& $nativeParamType->getItemType() instanceof ArrayType
				) {
					continue;
				}

				if ($isParamSuperType->no()) {
					$errors[] = sprintf(
						'PHPDoc tag @param for parameter $%s with type %s is incompatible with native type %s',
						$parameterName,
						$phpDocParamType->describe(),
						$nativeParamType->describe()
					);

				} elseif ($isParamSuperType->maybe()) {
					$errors[] = sprintf(
						'PHPDoc tag @param for parameter $%s with type %s is not subtype of native type %s',
						$parameterName,
						$phpDocParamType->describe(),
						$nativeParamType->describe()
					);
				}
			}
		}

		if ($resolvedPhpDoc->getReturnTag() !== null) {
			$phpDocReturnType = $resolvedPhpDoc->getReturnTag()->getType();

			if ($phpDocReturnType instanceof ErrorType) {
				$errors[] = 'PHPDoc tag @return contains unresolvable type';

			} else {
				$isReturnSuperType = $nativeReturnType->isSuperTypeOf($phpDocReturnType);
				if ($isReturnSuperType->no()) {
					$errors[] = sprintf(
						'PHPDoc tag @return with type %s is incompatible with native type %s',
						$phpDocReturnType->describe(),
						$nativeReturnType->describe()
					);

				} elseif ($isReturnSuperType->maybe()) {
					$errors[] = sprintf(
						'PHPDoc tag @return with type %s is not subtype of native type %s',
						$phpDocReturnType->describe(),
						$nativeReturnType->describe()
					);
				}
			}
		}

		return $errors;
	}

	/**
	 * @param Node\FunctionLike $node
	 * @param Scope $scope
	 * @return Type[]
	 */
	private function getNativeParameterTypes(\PhpParser\Node\FunctionLike $node, Scope $scope): array
	{
		$nativeParameterTypes = [];
		foreach ($node->getParams() as $parameter) {
			$isNullable = $scope->isParameterValueNullable($parameter);
			$nativeParameterTypes[$parameter->name] = $scope->getFunctionType(
				$parameter->type,
				$isNullable,
				$parameter->variadic
			);
		}

		return $nativeParameterTypes;
	}

	private function getNativeReturnType(\PhpParser\Node\FunctionLike $node, Scope $scope): Type
	{
		return $scope->getFunctionType($node->getReturnType(), false, false);
	}

}
