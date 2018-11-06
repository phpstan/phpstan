<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PhpParser\Node\Expr\Instanceof_;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\ClassNameNodePair;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;

class ExistingClassInInstanceOfRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Rules\ClassCaseSensitivityCheck */
	private $classCaseSensitivityCheck;

	/** @var bool */
	private $checkClassCaseSensitivity;

	public function __construct(
		Broker $broker,
		ClassCaseSensitivityCheck $classCaseSensitivityCheck,
		bool $checkClassCaseSensitivity
	)
	{
		$this->broker = $broker;
		$this->classCaseSensitivityCheck = $classCaseSensitivityCheck;
		$this->checkClassCaseSensitivity = $checkClassCaseSensitivity;
	}

	public function getNodeType(): string
	{
		return Instanceof_::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\Instanceof_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return RuleError[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$class = $node->class;
		if (!($class instanceof \PhpParser\Node\Name)) {
			return [];
		}

		$name = (string) $class;
		$lowercaseName = strtolower($name);

		if (in_array($lowercaseName, [
			'self',
			'static',
			'parent',
		], true)) {
			if (!$scope->isInClass()) {
				return [
					RuleErrorBuilder::message(sprintf('Using %s outside of class scope.', $lowercaseName))->line($class->getLine())->build(),
				];
			}

			return [];
		}

		if (!$this->broker->hasClass($name)) {
			return [
				RuleErrorBuilder::message(sprintf('Class %s not found.', $name))->line($class->getLine())->build(),
			];
		} elseif ($this->checkClassCaseSensitivity) {
			return $this->classCaseSensitivityCheck->checkClassNames([new ClassNameNodePair($name, $class)]);
		}

		return [];
	}

}
