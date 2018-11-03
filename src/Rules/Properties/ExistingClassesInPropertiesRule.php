<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PhpParser\Node;
use PhpParser\Node\Stmt\PropertyProperty;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\ClassNameNodePair;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;

class ExistingClassesInPropertiesRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Rules\ClassCaseSensitivityCheck */
	private $classCaseSensitivityCheck;

	/** @var bool */
	private $checkClassCaseSensitivity;

	public function __construct(
		Broker $broker,
		ClassCaseSensitivityCheck $classCaseSensitivityCheck,
		bool $checkClassCaseSensitivity
	)
	{
		$this->broker = $broker;
		$this->classCaseSensitivityCheck = $classCaseSensitivityCheck;
		$this->checkClassCaseSensitivity = $checkClassCaseSensitivity;
	}

	public function getNodeType(): string
	{
		return PropertyProperty::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\PropertyProperty $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return RuleError[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$scope->isInClass()) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$propertyReflection = $scope->getClassReflection()->getNativeProperty($node->name->name);
		$propertyType = $propertyReflection->getType();

		$errors = [];
		foreach ($propertyType->getReferencedClasses() as $referencedClass) {
			if ($this->broker->hasClass($referencedClass)) {
				if ($this->broker->getClass($referencedClass)->isTrait()) {
					$errors[] = RuleErrorBuilder::message(sprintf(
						'Property %s::$%s has invalid type %s.',
						$propertyReflection->getDeclaringClass()->getDisplayName(),
						$node->name->name,
						$referencedClass
					))->build();
				}
				continue;
			}

			$errors[] = RuleErrorBuilder::message(sprintf(
				'Property %s::$%s has unknown class %s as its type.',
				$propertyReflection->getDeclaringClass()->getDisplayName(),
				$node->name->name,
				$referencedClass
			))->build();
		}

		if ($this->checkClassCaseSensitivity) {
			$errors = array_merge(
				$errors,
				$this->classCaseSensitivityCheck->checkClassNames(array_map(static function (string $class) use ($node): ClassNameNodePair {
					return new ClassNameNodePair($class, $node);
				}, $propertyType->getReferencedClasses()))
			);
		}

		return $errors;
	}

}
