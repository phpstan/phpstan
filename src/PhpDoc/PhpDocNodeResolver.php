<?php declare(strict_types = 1);

namespace PHPStan\PhpDoc;

use PHPStan\Analyser\NameScope;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNullNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

class PhpDocNodeResolver
{

	/** @var TypeNodeResolver */
	private $typeNodeResolver;

	public function __construct(TypeNodeResolver $typeNodeResolver)
	{
		$this->typeNodeResolver = $typeNodeResolver;
	}

	public function resolve(PhpDocNode $phpDocNode, NameScope $nameScope): array
	{
		return [
			'var' => $this->resolveVarTags($phpDocNode, $nameScope),
			'method' => $this->resolveMethodTags($phpDocNode, $nameScope),
			'property' => $this->resolvePropertyTags($phpDocNode, $nameScope),
			'param' => $this->resolveParamTags($phpDocNode, $nameScope),
			'return' => $this->resolveReturnTag($phpDocNode, $nameScope),
		];
	}

	private function resolveVarTags(PhpDocNode $phpDocNode, NameScope $nameScope): array
	{
		$resolved = [];
		foreach ($phpDocNode->getVarTagValues() as $tagValue) {
			if ($tagValue->variableName !== '') {
				$variableName = substr($tagValue->variableName, 1);
				$resolved[$variableName] = !isset($resolved[$variableName])
					? $this->typeNodeResolver->resolve($tagValue->type, $nameScope)
					: new MixedType();

			} else {
				$resolved[] = $this->typeNodeResolver->resolve($tagValue->type, $nameScope);
			}
		}

		return $resolved;
	}

	private function resolvePropertyTags(PhpDocNode $phpDocNode, NameScope $nameScope): array
	{
		$resolved = [];

		foreach ($phpDocNode->getPropertyTagValues() as $tagValue) {
			$propertyName = substr($tagValue->propertyName, 1);
			$propertyType = !isset($resolved[$propertyName])
				? $this->typeNodeResolver->resolve($tagValue->type, $nameScope)
				: new MixedType();

			$resolved[$propertyName] = [
				'type' => $propertyType,
				'readable' => true,
				'writable' => true,
			];
		}

		foreach ($phpDocNode->getPropertyReadTagValues() as $tagValue) {
			$propertyName = substr($tagValue->propertyName, 1);
			$propertyType = !isset($resolved[$propertyName])
				? $this->typeNodeResolver->resolve($tagValue->type, $nameScope)
				: new MixedType();

			$resolved[$propertyName] = [
				'type' => $propertyType,
				'readable' => true,
				'writable' => false,
			];
		}

		foreach ($phpDocNode->getPropertyWriteTagValues() as $tagValue) {
			$propertyName = substr($tagValue->propertyName, 1);
			$propertyType = !isset($resolved[$propertyName])
				? $this->typeNodeResolver->resolve($tagValue->type, $nameScope)
				: new MixedType();

			$resolved[$propertyName] = [
				'type' => $propertyType,
				'readable' => false,
				'writable' => true,
			];
		}

		return $resolved;
	}

	private function resolveMethodTags(PhpDocNode $phpDocNode, NameScope $nameScope): array
	{
		$resolved = [];

		foreach ($phpDocNode->getMethodTagValues() as $tagValue) {
			$parameters = [];
			foreach ($tagValue->parameters as $parameterNode) {
				$parameterName = substr($parameterNode->parameterName, 1);
				$parameters[$parameterName] = [
					'type' => $parameterNode->type !== null ? $this->typeNodeResolver->resolve($parameterNode->type, $nameScope) : new MixedType(),
					'isPassedByReference' => $parameterNode->isReference,
					'isOptional' => $parameterNode->defaultValue !== null,
					'isVariadic' => $parameterNode->isVariadic,
				];

				if ($parameterNode->defaultValue instanceof ConstExprNullNode) {
					$parameters[$parameterName]['type'] = TypeCombinator::addNull($parameters[$parameterName]['type']);
				}
			}

			$resolved[$tagValue->methodName] = [
				'returnType' => $tagValue->returnType !== null ? $this->typeNodeResolver->resolve($tagValue->returnType, $nameScope) : new MixedType(),
				'isStatic' => $tagValue->isStatic,
				'parameters' => $parameters,
			];
		}

		return $resolved;
	}

	/**
	 * @param  PhpDocNode $phpDocNode
	 * @param  NameScope $nameScope
	 * @return Type[]
	 */
	private function resolveParamTags(PhpDocNode $phpDocNode, NameScope $nameScope): array
	{
		$resolved = [];
		foreach ($phpDocNode->getParamTagValues() as $tagValue) {
			$parameterName = substr($tagValue->parameterName, 1);
			$parameterType = !isset($resolved[$parameterName])
				? $this->typeNodeResolver->resolve($tagValue->type, $nameScope)
				: new MixedType();

			$resolved[$parameterName] = $tagValue->isVariadic
				? new ArrayType($parameterType)
				: $parameterType;
		}

		return $resolved;
	}

	/**
	 * @param  PhpDocNode $phpDocNode
	 * @param  NameScope $nameScope
	 * @return Type|null
	 */
	private function resolveReturnTag(PhpDocNode $phpDocNode, NameScope $nameScope)
	{
		foreach ($phpDocNode->getReturnTagValues() as $tagValue) {
			return $this->typeNodeResolver->resolve($tagValue->type, $nameScope);
		}

		return null;
	}

}
