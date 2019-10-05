<?php declare(strict_types = 1);

namespace PHPStan\Rules\PhpDoc;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Generics\GenericObjectTypeCheck;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ErrorType;
use PHPStan\Type\NeverType;
use PHPStan\Type\VerbosityLevel;

class IncompatiblePropertyPhpDocTypeRule implements Rule
{

	/** @var \PHPStan\Rules\Generics\GenericObjectTypeCheck */
	private $genericObjectTypeCheck;

	public function __construct(GenericObjectTypeCheck $genericObjectTypeCheck)
	{
		$this->genericObjectTypeCheck = $genericObjectTypeCheck;
	}

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

		$messages = array_merge($messages, $this->genericObjectTypeCheck->check(
			$phpDocType,
			sprintf(
				'PHPDoc tag @var for property %s::$%s contains generic type %%s but class %%s is not generic.',
				$propertyReflection->getDeclaringClass()->getDisplayName(),
				$propertyName
			),
			sprintf(
				'Generic type %%s in PHPDoc tag @var for property %s::$%s does not specify all template types of class %%s: %%s',
				$propertyReflection->getDeclaringClass()->getDisplayName(),
				$propertyName
			),
			sprintf(
				'Generic type %%s in PHPDoc tag @var for property %s::$%s specifies %%d template types, but class %%s supports only %%d: %%s',
				$propertyReflection->getDeclaringClass()->getDisplayName(),
				$propertyName
			),
			sprintf(
				'Type %%s in generic type %%s in PHPDoc tag @var for property %s::$%s is not subtype of template type %%s of class %%s.',
				$propertyReflection->getDeclaringClass()->getDisplayName(),
				$propertyName
			)
		));

		return $messages;
	}

}
