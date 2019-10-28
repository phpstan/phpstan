<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\MissingTypehintCheck;
use PHPStan\Type\MixedType;
use PHPStan\Type\VerbosityLevel;

/**
 * @implements \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\PropertyProperty>
 */
final class MissingPropertyTypehintRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Rules\MissingTypehintCheck */
	private $missingTypehintCheck;

	public function __construct(MissingTypehintCheck $missingTypehintCheck)
	{
		$this->missingTypehintCheck = $missingTypehintCheck;
	}

	public function getNodeType(): string
	{
		return \PhpParser\Node\Stmt\PropertyProperty::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if (!$scope->isInClass()) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$propertyReflection = $scope->getClassReflection()->getNativeProperty($node->name->name);
		$propertyType = $propertyReflection->getReadableType();
		if ($propertyType instanceof MixedType && !$propertyType->isExplicitMixed()) {
			return [
				sprintf(
					'Property %s::$%s has no typehint specified.',
					$propertyReflection->getDeclaringClass()->getDisplayName(),
					$node->name->name
				),
			];
		}

		$messages = [];
		foreach ($this->missingTypehintCheck->getIterableTypesWithMissingValueTypehint($propertyType) as $iterableType) {
			$messages[] = sprintf(
				'Property %s::$%s type has no value type specified in iterable type %s.',
				$propertyReflection->getDeclaringClass()->getDisplayName(),
				$node->name->name,
				$iterableType->describe(VerbosityLevel::typeOnly())
			);
		}

		return $messages;
	}

}
