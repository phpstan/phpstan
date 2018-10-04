<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ErrorType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\VerbosityLevel;

class ClassConstantRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Rules\RuleLevelHelper */
	private $ruleLevelHelper;

	/** @var \PHPStan\Rules\ClassCaseSensitivityCheck */
	private $classCaseSensitivityCheck;

	public function __construct(
		Broker $broker,
		RuleLevelHelper $ruleLevelHelper,
		ClassCaseSensitivityCheck $classCaseSensitivityCheck
	)
	{
		$this->broker = $broker;
		$this->ruleLevelHelper = $ruleLevelHelper;
		$this->classCaseSensitivityCheck = $classCaseSensitivityCheck;
	}

	public function getNodeType(): string
	{
		return ClassConstFetch::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\ClassConstFetch $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node->name instanceof Node\Identifier) {
			return [];
		}
		$constantName = $node->name->name;

		$class = $node->class;
		$messages = [];
		if ($class instanceof \PhpParser\Node\Name) {
			$className = (string) $class;
			$lowercasedClassName = strtolower($className);
			if (in_array($lowercasedClassName, ['self', 'static'], true)) {
				if (!$scope->isInClass()) {
					return [
						sprintf('Using %s outside of class scope.', $className),
					];
				}

				$className = $scope->getClassReflection()->getName();
			} elseif ($lowercasedClassName === 'parent') {
				if (!$scope->isInClass()) {
					return [
						sprintf('Using %s outside of class scope.', $className),
					];
				}
				$currentClassReflection = $scope->getClassReflection();
				if ($currentClassReflection->getParentClass() === false) {
					return [
						sprintf(
							'Access to parent::%s but %s does not extend any class.',
							$constantName,
							$currentClassReflection->getDisplayName()
						),
					];
				}
				$className = $currentClassReflection->getParentClass()->getName();
			} else {
				if (!$this->broker->hasClass($className)) {
					if (strtolower($constantName) === 'class') {
						return [
							sprintf('Class %s not found.', $className),
						];
					}

					return [
						sprintf('Access to constant %s on an unknown class %s.', $constantName, $className),
					];
				} else {
					$messages = $this->classCaseSensitivityCheck->checkClassNames([$className]);
				}

				$className = $this->broker->getClass($className)->getName();
			}

			$classType = new ObjectType($className);
		} else {
			$classTypeResult = $this->ruleLevelHelper->findTypeToCheck(
				$scope,
				$class,
				sprintf('Access to constant %s on an unknown class %%s.', $constantName),
				static function (Type $type) use ($constantName): bool {
					return $type->canAccessConstants()->yes() && $type->hasConstant($constantName);
				}
			);
			$classType = $classTypeResult->getType();
			if ($classType instanceof ErrorType) {
				return $classTypeResult->getUnknownClassErrors();
			}
		}

		if ((new StringType())->isSuperTypeOf($classType)->yes()) {
			return $messages;
		}

		$typeForDescribe = $classType;
		$classType = TypeCombinator::remove($classType, new StringType());

		if (!$classType->canAccessConstants()->yes()) {
			return array_merge($messages, [
				sprintf('Cannot access constant %s on %s.', $constantName, $typeForDescribe->describe(VerbosityLevel::typeOnly())),
			]);
		}

		if (strtolower($constantName) === 'class') {
			return $messages;
		}

		if (!$classType->hasConstant($constantName)) {
			return array_merge($messages, [
				sprintf(
					'Access to undefined constant %s::%s.',
					$typeForDescribe->describe(VerbosityLevel::typeOnly()),
					$constantName
				),
			]);
		}

		$constantReflection = $classType->getConstant($constantName);
		if (!$scope->canAccessConstant($constantReflection)) {
			return array_merge($messages, [
				sprintf(
					'Access to %s constant %s of class %s.',
					$constantReflection->isPrivate() ? 'private' : 'protected',
					$constantName,
					$constantReflection->getDeclaringClass()->getDisplayName()
				),
			]);
		}

		return $messages;
	}

}
