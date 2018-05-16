<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

class PropertyReflectionFinder
{

	/**
	 * @param \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch $propertyFetch
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return \PHPStan\Reflection\PropertyReflection|null
	 */
	public function findPropertyReflectionFromNode($propertyFetch, Scope $scope): ?\PHPStan\Reflection\PropertyReflection
	{
		if ($propertyFetch instanceof \PhpParser\Node\Expr\PropertyFetch) {
			if (!$propertyFetch->name instanceof \PhpParser\Node\Identifier) {
				return null;
			}
			$propertyHolderType = $scope->getType($propertyFetch->var);
			return $this->findPropertyReflection($propertyHolderType, $propertyFetch->name->name, $scope);
		}

		if (!$propertyFetch->name instanceof \PhpParser\Node\Identifier) {
			return null;
		}

		if ($propertyFetch->class instanceof \PhpParser\Node\Name) {
			$propertyHolderType = new ObjectType($scope->resolveName($propertyFetch->class));
		} else {
			$propertyHolderType = $scope->getType($propertyFetch->class);
		}

		return $this->findPropertyReflection($propertyHolderType, $propertyFetch->name->name, $scope);
	}

	private function findPropertyReflection(Type $propertyHolderType, string $propertyName, Scope $scope): ?\PHPStan\Reflection\PropertyReflection
	{
		if (!$propertyHolderType->hasProperty($propertyName)) {
			return null;
		}

		return $propertyHolderType->getProperty($propertyName, $scope);
	}

}
