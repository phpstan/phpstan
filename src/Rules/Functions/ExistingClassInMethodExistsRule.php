<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\ClassNameNodePair;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Constant\ConstantStringType;

class ExistingClassInMethodExistsRule implements Rule
{

	/** @var Broker */
	private $broker;

	/** @var ClassCaseSensitivityCheck */
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
		return FuncCall::class;
	}

	/**
	 * @param FuncCall $node
	 * @param Scope $scope
	 * @return RuleError[]|string[]
	 * @throws \PHPStan\Broker\ClassAutoloadingException
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node->name instanceof Name
			|| $node->name->toString() !== 'method_exists'
		) {
			return [];
		}

		if (!isset($node->args[0])) {
			return [];
		}

		$firstArg = $node->args[0];
		$firstArgType = $scope->getType($firstArg->value);

		if ($firstArgType instanceof ConstantStringType) {

			$className = $firstArgType->getValue();

			if (!$this->broker->hasClass($className)) {
				return [
					RuleErrorBuilder::message(sprintf('Class %s not found.', $className))
						->line($firstArg->value->getLine())
						->build(),
				];
			}

			if ($this->checkClassCaseSensitivity) {
				return $this->classCaseSensitivityCheck->checkClassNames([new ClassNameNodePair($className, $firstArg->value)]);
			}
		}

		return [];
	}

}
