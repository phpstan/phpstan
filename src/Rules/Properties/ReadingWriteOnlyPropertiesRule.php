<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleLevelHelper;

class ReadingWriteOnlyPropertiesRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Rules\Properties\PropertyDescriptor */
	private $propertyDescriptor;

	/** @var \PHPStan\Rules\Properties\PropertyReflectionFinder */
	private $propertyReflectionFinder;

	/** @var RuleLevelHelper */
	private $ruleLevelHelper;

	/** @var bool */
	private $checkThisOnly;

	public function __construct(
		PropertyDescriptor $propertyDescriptor,
		PropertyReflectionFinder $propertyReflectionFinder,
		RuleLevelHelper $ruleLevelHelper,
		bool $checkThisOnly
	)
	{
		$this->propertyDescriptor = $propertyDescriptor;
		$this->propertyReflectionFinder = $propertyReflectionFinder;
		$this->ruleLevelHelper = $ruleLevelHelper;
		$this->checkThisOnly = $checkThisOnly;
	}

	public function getNodeType(): string
	{
		return \PhpParser\Node\Expr::class;
	}

	/**
	 * @param \PhpParser\Node\Expr $node
	 * @param Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (
			!($node instanceof Node\Expr\PropertyFetch)
			&& !($node instanceof Node\Expr\StaticPropertyFetch)
		) {
			return [];
		}

		if (
			$node instanceof Node\Expr\PropertyFetch
			&& $this->checkThisOnly
			&& !$this->ruleLevelHelper->isThis($node->var)
		) {
			return [];
		}

		if ($scope->isInExpressionAssign($node)) {
			return [];
		}

		$propertyReflection = $this->propertyReflectionFinder->findPropertyReflectionFromNode($node, $scope);
		if ($propertyReflection === null) {
			return [];
		}
		if (!$scope->canAccessProperty($propertyReflection)) {
			return [];
		}

		if (!$propertyReflection->isReadable()) {
			$propertyDescription = $this->propertyDescriptor->describeProperty($propertyReflection, $node);

			return [
				sprintf(
					'%s is not readable.',
					$propertyDescription
				),
			];
		}

		return [];
	}

}
