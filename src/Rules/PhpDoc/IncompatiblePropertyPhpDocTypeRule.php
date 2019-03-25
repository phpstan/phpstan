<?php declare(strict_types = 1);

namespace PHPStan\Rules\PhpDoc;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Type\ErrorType;
use PHPStan\Type\NeverType;

class IncompatiblePropertyPhpDocTypeRule implements Rule
{

	public function getNodeType(): string
	{
		return Node\Stmt\PropertyProperty::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\PropertyProperty $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$scope->isInClass()) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$propertyName = $node->name->toString();
		$propertyReflection = $scope->getClassReflection()->getNativeProperty($propertyName);
		if (
			$propertyReflection->getType() instanceof ErrorType
			|| $propertyReflection->getType() instanceof NeverType
		) {
			return [
				sprintf(
					'PHPDoc tag @var for property %s::$%s contains unresolvable type.',
					$propertyReflection->getDeclaringClass()->getName(),
					$propertyName
				),
			];
		}

		return [];
	}

}
