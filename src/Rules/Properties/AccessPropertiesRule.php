<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class AccessPropertiesRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Rules\RuleLevelHelper */
	private $ruleLevelHelper;

	/** @var bool */
	private $reportMagicProperties;

	public function __construct(
		Broker $broker,
		RuleLevelHelper $ruleLevelHelper,
		bool $reportMagicProperties
	)
	{
		$this->broker = $broker;
		$this->ruleLevelHelper = $ruleLevelHelper;
		$this->reportMagicProperties = $reportMagicProperties;
	}

	public function getNodeType(): string
	{
		return PropertyFetch::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\PropertyFetch $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return (string|\PHPStan\Rules\RuleError)[]
	 */
	public function processNode(\PhpParser\Node $node, Scope $scope): array
	{
		if (!$node->name instanceof \PhpParser\Node\Identifier) {
			return [];
		}

		$name = $node->name->name;
		$typeResult = $this->ruleLevelHelper->findTypeToCheck(
			$scope,
			$node->var,
			sprintf('Access to property $%s on an unknown class %%s.', $name),
			static function (Type $type) use ($name): bool {
				return $type->canAccessProperties()->yes() && $type->hasProperty($name)->yes();
			}
		);
		$type = $typeResult->getType();
		if ($type instanceof ErrorType) {
			return $typeResult->getUnknownClassErrors();
		}

		if ($scope->isInExpressionAssign($node)) {
			return [];
		}

		if (!$type->canAccessProperties()->yes()) {
			return [
				sprintf('Cannot access property $%s on %s.', $name, $type->describe(VerbosityLevel::typeOnly())),
			];
		}

		if (!$type->hasProperty($name)->yes()) {
			if ($scope->isSpecified($node)) {
				return [];
			}

			$classNames = $typeResult->getReferencedClasses();
			if (!$this->reportMagicProperties) {
				foreach ($classNames as $className) {
					if (!$this->broker->hasClass($className)) {
						continue;
					}

					$classReflection = $this->broker->getClass($className);
					if (
						$classReflection->hasNativeMethod('__get')
						|| $classReflection->hasNativeMethod('__set')
					) {
						return [];
					}
				}
			}

			if (count($classNames) === 1) {
				$referencedClass = $typeResult->getReferencedClasses()[0];
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
					$type->describe(VerbosityLevel::typeOnly()),
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
					$type->describe(VerbosityLevel::typeOnly()),
					$name
				),
			];
		}

		return [];
	}

}
