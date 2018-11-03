<?php declare(strict_types = 1);

namespace PHPStan\Rules\Namespaces;

use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\ClassNameNodePair;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;

class ExistingNamesInGroupUseRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Rules\ClassCaseSensitivityCheck */
	private $classCaseSensitivityCheck;

	/** @var bool */
	private $checkFunctionNameCase;

	public function __construct(
		Broker $broker,
		ClassCaseSensitivityCheck $classCaseSensitivityCheck,
		bool $checkFunctionNameCase
	)
	{
		$this->broker = $broker;
		$this->classCaseSensitivityCheck = $classCaseSensitivityCheck;
		$this->checkFunctionNameCase = $checkFunctionNameCase;
	}

	public function getNodeType(): string
	{
		return \PhpParser\Node\Stmt\GroupUse::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\GroupUse $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return RuleError[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$errors = [];
		foreach ($node->uses as $use) {
			$error = null;

			/** @var Node\Name $name */
			$name = Node\Name::concat($node->prefix, $use->name);
			if (
				$node->type === Use_::TYPE_CONSTANT
				|| $use->type === Use_::TYPE_CONSTANT
			) {
				$error = $this->checkConstant($name);
			} elseif (
				$node->type === Use_::TYPE_FUNCTION
				|| $use->type === Use_::TYPE_FUNCTION
			) {
				$error = $this->checkFunction($name);
			} elseif ($use->type === Use_::TYPE_NORMAL) {
				$error = $this->checkClass($name);
			} else {
				throw new \PHPStan\ShouldNotHappenException();
			}

			if ($error === null) {
				continue;
			}

			$errors[] = $error;
		}

		return $errors;
	}

	private function checkConstant(Node\Name $name): ?RuleError
	{
		if (!$this->broker->hasConstant($name, null)) {
			return RuleErrorBuilder::message(sprintf('Used constant %s not found.', (string) $name))->build();
		}

		return null;
	}

	private function checkFunction(Node\Name $name): ?RuleError
	{
		if (!$this->broker->hasFunction($name, null)) {
			return RuleErrorBuilder::message(sprintf('Used function %s not found.', (string) $name))->build();
		}

		if ($this->checkFunctionNameCase) {
			$functionReflection = $this->broker->getFunction($name, null);
			$realName = $functionReflection->getName();
			$usedName = (string) $name;
			if (
				strtolower($realName) === strtolower($usedName)
				&& $realName !== $usedName
			) {
				return RuleErrorBuilder::message(sprintf(
					'Function %s used with incorrect case: %s.',
					$realName,
					$usedName
				))->build();
			}
		}

		return null;
	}

	private function checkClass(Node\Name $name): ?RuleError
	{
		$errors = $this->classCaseSensitivityCheck->checkClassNames([
			new ClassNameNodePair((string) $name, $name),
		]);
		if (count($errors) === 0) {
			return null;
		} elseif (count($errors) === 1) {
			return $errors[0];
		}

		throw new \PHPStan\ShouldNotHappenException();
	}

}
