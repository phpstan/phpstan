<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\VerbosityLevel;

class DefaultValueTypesAssignedToPropertiesRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Rules\RuleLevelHelper */
	private $ruleLevelHelper;

	public function __construct(RuleLevelHelper $ruleLevelHelper)
	{
		$this->ruleLevelHelper = $ruleLevelHelper;
	}

	public function getNodeType(): string
	{
		return Property::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\Property $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$scope->isInClass()) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$classReflection = $scope->getClassReflection();

		$errors = [];
		foreach ($node->props as $property) {
			if ($property->default === null) {
				continue;
			}

			if ($property->default instanceof Node\Expr\ConstFetch && (string) $property->default->name === 'null') {
				continue;
			}

			$propertyReflection = $classReflection->getNativeProperty($property->name->name);
			$propertyType = $propertyReflection->getType();
			$defaultValueType = $scope->getType($property->default);
			if ($this->ruleLevelHelper->accepts($propertyType, $defaultValueType, $scope->isDeclareStrictTypes())) {
				continue;
			}

			$errors[] = sprintf(
				'%s %s::$%s (%s) does not accept default value of type %s.',
				$node->isStatic() ? 'Static property' : 'Property',
				$classReflection->getDisplayName(),
				$property->name->name,
				$propertyType->describe(VerbosityLevel::typeOnly()),
				$defaultValueType->describe(VerbosityLevel::typeOnly())
			);
		}

		return $errors;
	}

}
