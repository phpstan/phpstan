<?php declare(strict_types = 1);

namespace PHPStan\Rules\Namespaces;

use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\ClassCaseSensitivityCheck;

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
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$messages = [];
		foreach ($node->uses as $use) {
			$message = null;

			/** @var Node\Name $name */
			$name = Node\Name::concat($node->prefix, $use->name);
			if (
				$node->type === Use_::TYPE_CONSTANT
				|| $use->type === Use_::TYPE_CONSTANT
			) {
				$message = $this->checkConstant($name);
			} elseif (
				$node->type === Use_::TYPE_FUNCTION
				|| $use->type === Use_::TYPE_FUNCTION
			) {
				$message = $this->checkFunction($name);
			} elseif ($use->type === Use_::TYPE_NORMAL) {
				$message = $this->checkClass($name);
			} else {
				throw new \PHPStan\ShouldNotHappenException();
			}

			if ($message === null) {
				continue;
			}

			$messages[] = $message;
		}

		return $messages;
	}

	private function checkConstant(Node\Name $name): ?string
	{
		if (!$this->broker->hasConstant($name, null)) {
			return sprintf('Used constant %s not found.', (string) $name);
		}

		return null;
	}

	private function checkFunction(Node\Name $name): ?string
	{
		if (!$this->broker->hasFunction($name, null)) {
			return sprintf('Used function %s not found.', (string) $name);
		}

		if ($this->checkFunctionNameCase) {
			$functionReflection = $this->broker->getFunction($name, null);
			$realName = $functionReflection->getName();
			$usedName = (string) $name;
			if (
				strtolower($realName) === strtolower($usedName)
				&& $realName !== $usedName
			) {
				return sprintf(
					'Function %s used with incorrect case: %s.',
					$realName,
					$usedName
				);
			}
		}

		return null;
	}

	private function checkClass(Node\Name $name): ?string
	{
		$messages = $this->classCaseSensitivityCheck->checkClassNames([(string) $name]);
		if (count($messages) === 0) {
			return null;
		} elseif (count($messages) === 1) {
			return $messages[0];
		}

		throw new \PHPStan\ShouldNotHappenException();
	}

}
