<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\NullType;
use PHPStan\Type\StaticType;
use PHPStan\Type\UnionType;

class AccessPropertiesRule implements \PHPStan\Rules\Rule
{

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	/**
	 * @var \PHPStan\Rules\RuleLevelHelper
	 */
	private $ruleLevelHelper;

	/**
	 * @var bool
	 */
	private $checkThisOnly;

	/**
	 * @var bool
	 */
	private $checkUnionTypes;

	public function __construct(
		Broker $broker,
		RuleLevelHelper $ruleLevelHelper,
		bool $checkThisOnly,
		bool $checkUnionTypes
	)
	{
		$this->broker = $broker;
		$this->ruleLevelHelper = $ruleLevelHelper;
		$this->checkThisOnly = $checkThisOnly;
		$this->checkUnionTypes = $checkUnionTypes;
	}

	public function getNodeType(): string
	{
		return PropertyFetch::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\PropertyFetch $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(\PhpParser\Node $node, Scope $scope): array
	{
		if (!is_string($node->name)) {
			return [];
		}

		if ($this->checkThisOnly && !$this->ruleLevelHelper->isThis($node->var)) {
			return [];
		}

		$type = $scope->getType($node->var);
		if (!$type instanceof NullType) {
			$type = \PHPStan\Type\TypeCombinator::removeNull($type);
		}
		if ($type instanceof MixedType || $type instanceof NeverType) {
			return [];
		}
		if ($type instanceof StaticType) {
			$type = $type->resolveStatic($type->getBaseClass());
		}

		$name = $node->name;
		$errors = [];
		$referencedClasses = $type->getReferencedClasses();
		foreach ($referencedClasses as $referencedClass) {
			if (!$this->broker->hasClass($referencedClass)) {
				$errors[] = sprintf(
					'Access to property $%s on an unknown class %s.',
					$name,
					$referencedClass
				);
			}
		}

		if (count($errors) > 0) {
			return $errors;
		}

		if (!$this->checkUnionTypes && $type instanceof UnionType) {
			return [];
		}

		if (!$type->canAccessProperties()) {
			return [
				sprintf('Cannot access property $%s on %s.', $node->name, $type->describe()),
			];
		}

		if (!$type->hasProperty($name)) {
			if ($scope->isSpecified($node)) {
				return [];
			}

			if (count($referencedClasses) === 1) {
				$referencedClass = $referencedClasses[0];
				$propertyClassReflection = $this->broker->getClass($referencedClass);
				$parentClassReflection = $propertyClassReflection->getParentClass();
				while ($parentClassReflection !== false) {
					if ($parentClassReflection->hasProperty($name)) {
						return [
							sprintf(
								'Access to private property $%s of parent class %s.',
								$name,
								$parentClassReflection->getDisplayName()
							),
						];
					}

					$parentClassReflection = $parentClassReflection->getParentClass();
				}
			}

			return [
				sprintf(
					'Access to an undefined property %s::$%s.',
					$type->describe(),
					$name
				),
			];
		}

		$propertyReflection = $type->getProperty($name, $scope);
		if (!$scope->canAccessProperty($propertyReflection)) {
			return [
				sprintf(
					'Access to %s property %s::$%s.',
					$propertyReflection->isPrivate() ? 'private' : 'protected',
					$type->describe(),
					$name
				),
			];
		}

		return [];
	}

}
