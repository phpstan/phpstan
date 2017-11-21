<?php declare(strict_types = 1);

namespace PHPStan\Rules\PhpDoc;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
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

		$resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc($scope->getFile(), $docComment->getText());
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

			} else {
				$nativeParamType = $nativeParameterTypes[$parameterName];
				$isParamSuperset = $nativeParamType->isSupersetOf($phpDocParamType);

				if ($isParamSuperset->no()) {
					$errors[] = sprintf(
						'PHPDoc tag @param for parameter $%s with type %s is incompatible with native type %s',
						$parameterName,
						$phpDocParamType->describe(),
						$nativeParamType->describe()
					);

				} elseif ($isParamSuperset->maybe()) {
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

			$isReturnSuperset = $nativeReturnType->isSupersetOf($phpDocReturnType);

			if ($isReturnSuperset->no()) {
				$errors[] = sprintf(
					'PHPDoc tag @return with type %s is incompatible with native type %s',
					$phpDocReturnType->describe(),
					$nativeReturnType->describe()
				);

			} elseif ($isReturnSuperset->maybe()) {
				$errors[] = sprintf(
					'PHPDoc tag @return with type %s is not subtype of native type %s',
					$phpDocReturnType->describe(),
					$nativeReturnType->describe()
				);
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
