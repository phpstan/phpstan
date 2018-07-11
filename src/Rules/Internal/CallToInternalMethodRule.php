<?php declare(strict_types = 1);

namespace PHPStan\Rules\Internal;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\InternableReflection;
use PHPStan\Type\TypeUtils;

class CallToInternalMethodRule implements \PHPStan\Rules\Rule
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
		return MethodCall::class;
	}

	/**
	 * @param MethodCall $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[] errors
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node->name instanceof Identifier) {
			return [];
		}

		$methodName = $node->name->name;
		$methodCalledOnType = $scope->getType($node->var);
		$referencedClasses = TypeUtils::getDirectClassNames($methodCalledOnType);

		foreach ($referencedClasses as $referencedClass) {
			try {
				$classReflection = $this->broker->getClass($referencedClass);
				$methodReflection = $classReflection->getMethod($methodName, $scope);

				if (!$methodReflection instanceof InternableReflection) {
					continue;
				}

				if (!$methodReflection->isInternal()) {
					continue;
				}

				$methodFile = $methodReflection->getDeclaringClass()->getFileName();
				if ($methodFile === false) {
					continue;
				}

				if ($this->internalScopeHelper->isFileInInternalPaths($methodFile)) {
					continue;
				}

				return [sprintf(
					'Call to internal method %s() of class %s.',
					$methodReflection->getName(),
					$methodReflection->getDeclaringClass()->getName()
				)];
			} catch (\PHPStan\Broker\ClassNotFoundException $e) {
				// Other rules will notify if the class is not found
			} catch (\PHPStan\Reflection\MissingMethodFromReflectionException $e) {
				// Other rules will notify if the the method is not found
			}
		}

		return [];
	}

}
