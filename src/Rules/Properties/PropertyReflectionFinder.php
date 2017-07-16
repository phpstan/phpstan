<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;

class PropertyReflectionFinder
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	public function __construct(Broker $broker)
	{
		$this->broker = $broker;
	}

	/**
	 * @param \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch $propertyFetch
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return \PHPStan\Reflection\PropertyReflection|null
	 */
	public function findPropertyReflectionFromNode($propertyFetch, Scope $scope)
	{
		if ($propertyFetch instanceof \PhpParser\Node\Expr\PropertyFetch) {
			if (!is_string($propertyFetch->name)) {
				return null;
			}
			$propertyHolderType = $scope->getType($propertyFetch->var);
			if ($propertyHolderType->getClass() === null) {
				return null;
			}

			return $this->findPropertyReflection($propertyHolderType->getClass(), $propertyFetch->name, $scope);
		} elseif ($propertyFetch instanceof \PhpParser\Node\Expr\StaticPropertyFetch) {
			if (
				!($propertyFetch->class instanceof \PhpParser\Node\Name)
				|| !is_string($propertyFetch->name)
			) {
				return null;
			}

			return $this->findPropertyReflection($scope->resolveName($propertyFetch->class), $propertyFetch->name, $scope);
		}

		return null;
	}

	/**
	 * @param string $className
	 * @param string $propertyName
	 * @param Scope $scope
	 * @return \PHPStan\Reflection\PropertyReflection|null
	 */
	private function findPropertyReflection(string $className, string $propertyName, Scope $scope)
	{
		if (!$this->broker->hasClass($className)) {
			return null;
		}
		$propertyClass = $this->broker->getClass($className);
		if (!$propertyClass->hasProperty($propertyName)) {
			return null;
		}

		return $propertyClass->getProperty($propertyName, $scope);
	}

}
