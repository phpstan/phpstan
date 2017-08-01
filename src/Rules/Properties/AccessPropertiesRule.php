<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TrinaryLogic;

class AccessPropertiesRule implements \PHPStan\Rules\Rule
{

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	/**
	 * @var \PHPStan\Rules\RuleLevelHelper
	 */
	private $ruleLevelHelper;

	/**
	 * @var bool
	 */
	private $checkThisOnly;

	public function __construct(
		Broker $broker,
		RuleLevelHelper $ruleLevelHelper,
		bool $checkThisOnly
	)
	{
		$this->broker = $broker;
		$this->ruleLevelHelper = $ruleLevelHelper;
		$this->checkThisOnly = $checkThisOnly;
	}

	public function getNodeType(): string
	{
		return PropertyFetch::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\PropertyFetch $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(\PhpParser\Node $node, Scope $scope): array
	{
		if (!is_string($node->name)) {
			return [];
		}

		if ($this->checkThisOnly && !$this->ruleLevelHelper->isThis($node->var)) {
			return [];
		}

		$type = $scope->getType($node->var);

		if ($type instanceof MixedType) {
			return [];
		}

		if (!$type->canAccessProperties()) {
			return [
				sprintf('Cannot access property $%s on %s.', $node->name, $type->describe()),
			];
		}

		$propertyName = $node->name;
		$scopeName = $scope->getName();

		if ($scope->isSpecified($node) || !$type instanceof ObjectType) {
			return [];
		}

		if ($type->hasProperty($propertyName, $scopeName) === TrinaryLogic::NO) {
			return [
				sprintf(
					'Access to unknown property $%s on type %s.',
					$propertyName,
					$type->describe()
				),
			];
		}

		if ($type->hasProperty($propertyName, $scopeName) === TrinaryLogic::MAYBE) {
			return [
				sprintf(
					'Access to maybe unknown property $%s on type %s.',
					$propertyName,
					$type->describe()
				),
			];
		}

		return [];
	}

}
