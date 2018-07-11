<?php declare(strict_types = 1);

namespace PHPStan\Rules\Internal;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\InternableReflection;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;

class AccessInternalStaticPropertyRule implements \PHPStan\Rules\Rule
{

	/** @var Broker */
	private $broker;

	/** @var RuleLevelHelper */
	private $ruleLevelHelper;

	/** @var InternalScopeHelper */
	private $internalScopeHelper;

	public function __construct(
		Broker $broker,
		RuleLevelHelper $ruleLevelHelper,
		InternalScopeHelper $internalScopeHelper
	)
	{
		$this->broker = $broker;
		$this->ruleLevelHelper = $ruleLevelHelper;
		$this->internalScopeHelper = $internalScopeHelper;
	}

	public function getNodeType(): string
	{
		return StaticPropertyFetch::class;
	}

	/**
	 * @param StaticPropertyFetch $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[] errors
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node->name instanceof Identifier) {
			return [];
		}

		$propertyName = $node->name->name;
		$referencedClasses = [];

		if ($node->class instanceof Name) {
			$referencedClasses[] = (string) $node->class;
		} else {
			$classTypeResult = $this->ruleLevelHelper->findTypeToCheck(
				$scope,
				$node->class,
				'', // We don't care about the error message
				function (Type $type) use ($propertyName) {
					return $type->canAccessProperties()->yes() && $type->hasProperty($propertyName);
				}
			);

			if ($classTypeResult->getType() instanceof ErrorType) {
				return [];
			}

			$referencedClasses = $classTypeResult->getReferencedClasses();
		}

		foreach ($referencedClasses as $referencedClass) {
			try {
				$class = $this->broker->getClass($referencedClass);
				$property = $class->getProperty($propertyName, $scope);
			} catch (\PHPStan\Broker\ClassNotFoundException $e) {
				continue;
			} catch (\PHPStan\Reflection\MissingPropertyFromReflectionException $e) {
				continue;
			}

			if (!$property instanceof InternableReflection) {
				continue;
			}

			if (!$property->isInternal()) {
				continue;
			}

			$propertyFile = $property->getDeclaringClass()->getFileName();
			if ($propertyFile === false) {
				continue;
			}

			if ($this->internalScopeHelper->isFileInInternalPaths($propertyFile)) {
				continue;
			}

			return [sprintf(
				'Access to internal static property $%s of class %s.',
				$propertyName,
				$referencedClass
			)];
		}

		return [];
	}

}
