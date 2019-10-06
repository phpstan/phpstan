<?php declare(strict_types = 1);

namespace PHPStan\Rules\Generics;

use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Generic\TemplateType;
use PHPStan\Type\Generic\TemplateTypeHelper;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\VerbosityLevel;

class GenericObjectTypeCheck
{

	/**
	 * @param \PHPStan\Type\Type $phpDocType
	 * @param string $classNotGenericMessage
	 * @param string $notEnoughTypesMessage
	 * @param string $extraTypesMessage
	 * @param string $typeIsNotSubtypeMessage
	 * @return \PHPStan\Rules\RuleError[]
	 */
	public function check(
		Type $phpDocType,
		string $classNotGenericMessage,
		string $notEnoughTypesMessage,
		string $extraTypesMessage,
		string $typeIsNotSubtypeMessage
	): array
	{
		$genericTypes = $this->getGenericTypes($phpDocType);
		$messages = [];
		foreach ($genericTypes as $genericType) {
			$classReflection = $genericType->getClassReflection();
			if ($classReflection === null) {
				continue;
			}
			if (!$classReflection->isGeneric()) {
				$messages[] = RuleErrorBuilder::message(sprintf($classNotGenericMessage, $genericType->describe(VerbosityLevel::typeOnly()), $classReflection->getDisplayName()))->build();
				continue;
			}

			$templateTypes = array_values($classReflection->getTemplateTypeMap()->getTypes());

			$genericTypeTypes = $genericType->getTypes();
			$templateTypesCount = count($templateTypes);
			$genericTypeTypesCount = count($genericTypeTypes);
			if ($templateTypesCount > $genericTypeTypesCount) {
				$messages[] = RuleErrorBuilder::message(sprintf(
					$notEnoughTypesMessage,
					$genericType->describe(VerbosityLevel::typeOnly()),
					$classReflection->getDisplayName(false),
					implode(', ', array_keys($classReflection->getTemplateTypeMap()->getTypes()))
				))->build();
			} elseif ($templateTypesCount < $genericTypeTypesCount) {
				$messages[] = RuleErrorBuilder::message(sprintf(
					$extraTypesMessage,
					$genericType->describe(VerbosityLevel::typeOnly()),
					$genericTypeTypesCount,
					$classReflection->getDisplayName(false),
					$templateTypesCount,
					implode(', ', array_keys($classReflection->getTemplateTypeMap()->getTypes()))
				))->build();
			}

			foreach ($templateTypes as $i => $templateType) {
				if (!isset($genericTypeTypes[$i])) {
					continue;
				}

				$boundType = $templateType;
				if ($templateType instanceof TemplateType) {
					$boundType = $templateType->getBound();
				}
				$genericTypeType = $genericTypeTypes[$i];
				if ($boundType->isSuperTypeOf($genericTypeType)->yes()) {
					continue;
				}

				$messages[] = RuleErrorBuilder::message(sprintf(
					$typeIsNotSubtypeMessage,
					$genericTypeType->describe(VerbosityLevel::typeOnly()),
					$genericType->describe(VerbosityLevel::typeOnly()),
					$templateType->describe(VerbosityLevel::typeOnly()),
					$classReflection->getDisplayName(false)
				))->build();
			}
		}

		return $messages;
	}

	/**
	 * @param \PHPStan\Type\Type $phpDocType
	 * @return \PHPStan\Type\Generic\GenericObjectType[]
	 */
	private function getGenericTypes(Type $phpDocType): array
	{
		if ($phpDocType instanceof GenericObjectType) {
			$resolvedType = TemplateTypeHelper::resolveToBounds($phpDocType);
			if (!$resolvedType instanceof GenericObjectType) {
				throw new \PHPStan\ShouldNotHappenException();
			}
			return [$resolvedType];
		}

		$genericObjectTypes = [];
		TypeTraverser::map($phpDocType, static function (Type $type, callable $traverse) use (&$genericObjectTypes): Type {
			if ($type instanceof GenericObjectType) {
				$resolvedType = TemplateTypeHelper::resolveToBounds($type);
				if (!$resolvedType instanceof GenericObjectType) {
					throw new \PHPStan\ShouldNotHappenException();
				}
				$genericObjectTypes[] = $resolvedType;
			}
			$traverse($type);
			return $type;
		});

		return $genericObjectTypes;
	}

}
