<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Rules\Rule;

class NewStaticRule implements Rule
{

	public function getNodeType(): string
	{
		return Node\Expr\New_::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\New_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node->class instanceof Node\Name) {
			return [];
		}

		if (!$scope->isInClass()) {
			return [];
		}

		if (strtolower($node->class->toString()) !== 'static') {
			return [];
		}

		$classReflection = $scope->getClassReflection();
		if ($classReflection->isFinal()) {
			return [];
		}

		$messages = [
			"Unsafe usage of new static().\n💡 Consider making the class or the constructor final.",
		];
		if (!$classReflection->hasConstructor()) {
			return $messages;
		}

		$constructor = $classReflection->getConstructor();
		if ($constructor->getPrototype()->getDeclaringClass()->isInterface()) {
			return [];
		}

		if ($constructor instanceof PhpMethodReflection) {
			if ($constructor->isFinal()) {
				return [];
			}

			$prototype = $constructor->getPrototype();
			if ($prototype->isAbstract()) {
				return [];
			}
		}

		return $messages;
	}

}
