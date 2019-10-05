<?php declare(strict_types = 1);

namespace PHPStan\Rules\PhpDoc;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ErrorType;
use PHPStan\Type\NeverType;
use PHPStan\Type\VerbosityLevel;

class IncompatiblePropertyPhpDocTypeRule implements Rule
{

	public function getNodeType(): string
	{
		return Node\Stmt\PropertyProperty::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\PropertyProperty $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return \PHPStan\Rules\RuleError[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$scope->isInClass()) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$propertyName = $node->name->toString();
		$propertyReflection = $scope->getClassReflection()->getNativeProperty($propertyName);

		if (!$propertyReflection->hasPhpDoc()) {
			return [];
		}

		$phpDocType = $propertyReflection->getPhpDocType();

		$messages = [];
		if (
			$phpDocType instanceof ErrorType
			|| ($phpDocType instanceof NeverType && !$phpDocType->isExplicit())
		) {
			$messages[] = RuleErrorBuilder::message(sprintf(
				'PHPDoc tag @var for property %s::$%s contains unresolvable type.',
				$propertyReflection->getDeclaringClass()->getName(),
				$propertyName
			))->build();
		}

		$nativeType = $propertyReflection->getNativeType();
		$isSuperType = $nativeType->isSuperTypeOf($phpDocType);
		if ($isSuperType->no()) {
			$messages[] = RuleErrorBuilder::message(sprintf(
				'PHPDoc tag @var for property %s::$%s with type %s is incompatible with native type %s.',
				$propertyReflection->getDeclaringClass()->getDisplayName(),
				$propertyName,
				$phpDocType->describe(VerbosityLevel::typeOnly()),
				$nativeType->describe(VerbosityLevel::typeOnly())
			))->build();

		} elseif ($isSuperType->maybe()) {
			$messages[] = RuleErrorBuilder::message(sprintf(
				'PHPDoc tag @var for property %s::$%s with type %s is not subtype of native type %s.',
				$propertyReflection->getDeclaringClass()->getDisplayName(),
				$propertyName,
				$phpDocType->describe(VerbosityLevel::typeOnly()),
				$nativeType->describe(VerbosityLevel::typeOnly())
			))->build();
		}

		return $messages;
	}

}
