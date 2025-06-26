<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Analyser\NullsafeOperatorHelper;
use PHPStan\Analyser\Scope;
use PHPStan\DependencyInjection\AutowiredParameter;
use PHPStan\DependencyInjection\AutowiredService;
use PHPStan\Internal\SprintfHelper;
use PHPStan\Php\PhpVersion;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\StaticType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use function array_keys;
use function array_map;
use function array_merge;
use function count;
use function in_array;
use function sprintf;

#[AutowiredService]
final class AccessPropertiesCheck
{

	public function __construct(
		private ReflectionProvider $reflectionProvider,
		private RuleLevelHelper $ruleLevelHelper,
		private PhpVersion $phpVersion,
		#[AutowiredParameter]
		private bool $reportMagicProperties,
		#[AutowiredParameter]
		private bool $checkDynamicProperties,
	)
	{
	}

	/**
	 * @return list<IdentifierRuleError>
	 */
	public function check(PropertyFetch $node, Scope $scope, bool $write): array
	{
		if ($node->name instanceof Identifier) {
			$names = [$node->name->name];
		} else {
			$names = array_map(static fn (ConstantStringType $type): string => $type->getValue(), $scope->getType($node->name)->getConstantStrings());
		}

		$errors = [];
		foreach ($names as $name) {
			$errors = array_merge($errors, $this->processSingleProperty($scope, $node, $name, $write));
		}

		return $errors;
	}

	/**
	 * @return list<IdentifierRuleError>
	 */
	private function processSingleProperty(Scope $scope, PropertyFetch $node, string $name, bool $write): array
	{
		$typeResult = $this->ruleLevelHelper->findTypeToCheck(
			$scope,
			NullsafeOperatorHelper::getNullsafeShortcircuitedExprRespectingScope($scope, $node->var),
			sprintf('Access to property $%s on an unknown class %%s.', SprintfHelper::escapeFormatString($name)),
			static fn (Type $type): bool => $type->canAccessProperties()->yes() && $type->hasProperty($name)->yes(),
		);
		$type = $typeResult->getType();
		if ($type instanceof ErrorType) {
			return $typeResult->getUnknownClassErrors();
		}

		if ($scope->isInExpressionAssign($node)) {
			return [];
		}

		$typeForDescribe = $type;
		if ($type instanceof StaticType) {
			$typeForDescribe = $type->getStaticObjectType();
		}

		if ($type->canAccessProperties()->no() || $type->canAccessProperties()->maybe() && !$scope->isUndefinedExpressionAllowed($node)) {
			return [
				RuleErrorBuilder::message(sprintf(
					'Cannot access property $%s on %s.',
					$name,
					$typeForDescribe->describe(VerbosityLevel::typeOnly()),
				))->identifier('property.nonObject')->build(),
			];
		}

		$has = $type->hasProperty($name);
		if (!$has->no() && $this->canAccessUndefinedProperties($scope, $node)) {
			return [];
		}

		if (!$has->yes()) {
			if ($scope->hasExpressionType($node)->yes()) {
				return [];
			}

			$classNames = $type->getObjectClassNames();
			if (!$this->reportMagicProperties) {
				foreach ($classNames as $className) {
					if (!$this->reflectionProvider->hasClass($className)) {
						continue;
					}

					$classReflection = $this->reflectionProvider->getClass($className);
					if (
						$classReflection->hasNativeMethod('__get')
						|| $classReflection->hasNativeMethod('__set')
					) {
						return [];
					}
				}
			}

			$propertyTags = [];
			if (count($classNames) === 1) {
				$propertyClassReflection = $this->reflectionProvider->getClass($classNames[0]);
				$parentClassReflection = $propertyClassReflection->getParentClass();
				$propertyTags = $propertyClassReflection->getPropertyTags();
				while ($parentClassReflection !== null) {
					if ($parentClassReflection->hasProperty($name)) {
						if ($write) {
							if ($scope->canWriteProperty($parentClassReflection->getProperty($name, $scope))) {
								return [];
							}
						} elseif ($scope->canReadProperty($parentClassReflection->getProperty($name, $scope))) {
							return [];
						}

						return [
							RuleErrorBuilder::message(sprintf(
								'Access to private property $%s of parent class %s.',
								$name,
								$parentClassReflection->getDisplayName(),
							))->identifier('property.private')->build(),
						];
					}
					$propertyTags += $parentClassReflection->getPropertyTags();
					$parentClassReflection = $parentClassReflection->getParentClass();
				}
			}

			if (in_array($name, array_keys($propertyTags), true)) {
				return [];
			}

			if ($node->name instanceof Expr) {
				$propertyExistsExpr = new FuncCall(new FullyQualified('property_exists'), [
					new Arg($node->var),
					new Arg($node->name),
				]);

				if ($scope->getType($propertyExistsExpr)->isTrue()->yes()) {
					return [];
				}
			}

			$ruleErrorBuilder = RuleErrorBuilder::message(sprintf(
				'Access to an undefined property %s::$%s.',
				$typeForDescribe->describe(VerbosityLevel::typeOnly()),
				$name,
			))->identifier('property.notFound');
			if ($typeResult->getTip() !== null) {
				$ruleErrorBuilder->tip($typeResult->getTip());
			} else {
				$ruleErrorBuilder->tip('Learn more: <fg=cyan>https://phpstan.org/blog/solving-phpstan-access-to-undefined-property</>');
			}

			return [
				$ruleErrorBuilder->build(),
			];
		}

		$propertyReflection = $type->getProperty($name, $scope);
		if ($propertyReflection->isStatic()) {
			return [
				RuleErrorBuilder::message(sprintf(
					'Non-static access to static property %s::$%s.',
					$propertyReflection->getDeclaringClass()->getDisplayName(),
					$name,
				))->identifier('staticProperty.nonStaticAccess')->build(),
			];
		}

		if ($write) {
			if ($scope->canWriteProperty($propertyReflection)) {
				return [];
			}
		} elseif ($scope->canReadProperty($propertyReflection)) {
			return [];
		}

		if (
			!$this->phpVersion->supportsAsymmetricVisibility()
			|| !$write
			|| (!$propertyReflection->isPrivateSet() && !$propertyReflection->isProtectedSet())
		) {
			return [
				RuleErrorBuilder::message(sprintf(
					'Access to %s property %s::$%s.',
					$propertyReflection->isPrivate() ? 'private' : 'protected',
					$type->describe(VerbosityLevel::typeOnly()),
					$name,
				))->identifier(sprintf('property.%s', $propertyReflection->isPrivate() ? 'private' : 'protected'))->build(),
			];
		}

		return [
			RuleErrorBuilder::message(sprintf(
				'Assign to %s property %s::$%s.',
				$propertyReflection->isPrivateSet() ? 'private(set)' : 'protected(set)',
				$type->describe(VerbosityLevel::typeOnly()),
				$name,
			))->identifier(sprintf('assign.property%s', $propertyReflection->isPrivateSet() ? 'PrivateSet' : 'ProtectedSet'))->build(),
		];
	}

	private function canAccessUndefinedProperties(Scope $scope, Expr $node): bool
	{
		return $scope->isUndefinedExpressionAllowed($node) && !$this->checkDynamicProperties;
	}

}
