<?php declare(strict_types = 1);

namespace PHPStan\Rules\Generics;

use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\VerbosityLevel;

class GenericObjectTypeCheck
{

	/**
	 * @param \PHPStan\Type\Type $phpDocType
	 * @param string $classNotGenericMessage
	 * @return \PHPStan\Rules\RuleError[]
	 */
	public function check(
		Type $phpDocType,
		string $classNotGenericMessage
	): array
	{
		$genericTypes = $this->getGenericTypes($phpDocType);
		$messages = [];
		foreach ($genericTypes as $genericType) {
			$classReflection = $genericType->getClassReflection();
			if ($classReflection === null) {
				continue;
			}
			if ($classReflection->isGeneric()) {
				continue;
			}

			$messages[] = RuleErrorBuilder::message(sprintf($classNotGenericMessage, $genericType->describe(VerbosityLevel::typeOnly()), $classReflection->getDisplayName()))->build();
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
			return [$phpDocType];
		}

		$genericObjectTypes = [];
		TypeTraverser::map($phpDocType, static function (Type $type, callable $traverse) use (&$genericObjectTypes): Type {
			if ($type instanceof GenericObjectType) {
				$genericObjectTypes[] = $type;
			}
			$traverse($type);
			return $type;
		});

		return $genericObjectTypes;
	}

}
