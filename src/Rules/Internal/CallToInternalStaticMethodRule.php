<?php declare(strict_types = 1);

namespace PHPStan\Rules\Internal;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\InternableReflection;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;

class CallToInternalStaticMethodRule implements \PHPStan\Rules\Rule
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
		return StaticCall::class;
	}

	/**
	 * @param StaticCall $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[] errors
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node->name instanceof Identifier) {
			return [];
		}

		$methodName = $node->name->name;
		$referencedClasses = [];

		if ($node->class instanceof Name) {
			$referencedClasses[] = $scope->resolveName($node->class);
		} else {
			$classTypeResult = $this->ruleLevelHelper->findTypeToCheck(
				$scope,
				$node->class,
				'', // We don't care about the error message
				function (Type $type) use ($methodName) {
					return $type->canCallMethods()->yes() && $type->hasMethod($methodName);
				}
			);

			if ($classTypeResult->getType() instanceof ErrorType) {
				return [];
			}

			$referencedClasses = $classTypeResult->getReferencedClasses();
		}

		$errors = [];

		foreach ($referencedClasses as $referencedClass) {
			try {
				$class = $this->broker->getClass($referencedClass);
				$methodReflection = $class->getMethod($methodName, $scope);
			} catch (\PHPStan\Broker\ClassNotFoundException $e) {
				continue;
			} catch (\PHPStan\Reflection\MissingMethodFromReflectionException $e) {
				continue;
			}

			$classFile = $class->getFileName();
			if ($classFile === false) {
				continue;
			}

			if ($this->internalScopeHelper->isFileInInternalPaths($classFile)) {
				continue;
			}

			if ($class->isInternal()) {
				$errors[] = sprintf(
					'Call to method %s() of internal class %s.',
					$methodReflection->getName(),
					$methodReflection->getDeclaringClass()->getName()
				);
			}

			if (!$methodReflection instanceof InternableReflection || !$methodReflection->isInternal()) {
				continue;
			}

			$errors[] = sprintf(
				'Call to internal method %s() of class %s.',
				$methodReflection->getName(),
				$methodReflection->getDeclaringClass()->getName()
			);
		}

		return $errors;
	}

}
