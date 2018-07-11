<?php declare(strict_types = 1);

namespace PHPStan\Rules\Internal;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\InternableReflection;
use PHPStan\Type\TypeUtils;

class AccessInternalPropertyRule implements \PHPStan\Rules\Rule
{

	/** @var Broker */
	private $broker;

	/** @var InternalScopeHelper */
	private $internalScopeHelper;

	public function __construct(Broker $broker, InternalScopeHelper $internalScopeHelper)
	{
		$this->broker = $broker;
		$this->internalScopeHelper = $internalScopeHelper;
	}

	public function getNodeType(): string
	{
		return PropertyFetch::class;
	}

	/**
	 * @param PropertyFetch $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[] errors
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node->name instanceof Identifier) {
			return [];
		}

		$propertyName = $node->name->name;
		$propertyAccessedOnType = $scope->getType($node->var);
		$referencedClasses = TypeUtils::getDirectClassNames($propertyAccessedOnType);

		foreach ($referencedClasses as $referencedClass) {
			try {
				$classReflection = $this->broker->getClass($referencedClass);
				$propertyReflection = $classReflection->getProperty($propertyName, $scope);

				if (!$propertyReflection instanceof InternableReflection) {
					continue;
				}

				if (!$propertyReflection->isInternal()) {
					continue;
				}

				$propertyFile = $propertyReflection->getDeclaringClass()->getFileName();
				if ($propertyFile === false) {
					continue;
				}

				if ($this->internalScopeHelper->isFileInInternalPaths($propertyFile)) {
					continue;
				}

				return [sprintf(
					'Access to internal property $%s of class %s.',
					$propertyName,
					$propertyReflection->getDeclaringClass()->getName()
				)];
			} catch (\PHPStan\Broker\ClassNotFoundException $e) {
				// Other rules will notify if the class is not found
			} catch (\PHPStan\Reflection\MissingPropertyFromReflectionException $e) {
				// Other rules will notify if the property is not found
			}
		}

		return [];
	}

}
