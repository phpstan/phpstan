<?php declare(strict_types = 1);

namespace PHPStan\Rules\Namespaces;

use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\ClassCaseSensitivityCheck;

class ExistingNamesInGroupUseRule implements \PHPStan\Rules\Rule
{

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	/**
	 * @var \PHPStan\Rules\ClassCaseSensitivityCheck
	 */
	private $classCaseSensitivityCheck;

	public function __construct(
		Broker $broker,
		ClassCaseSensitivityCheck $classCaseSensitivityCheck
	)
	{
		$this->broker = $broker;
		$this->classCaseSensitivityCheck = $classCaseSensitivityCheck;
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

			if ($message !== null) {
				$messages[] = $message;
			}
		}

		return $messages;
	}

	/**
	 * @param \PhpParser\Node\Name $name
	 * @return string|null
	 */
	private function checkConstant(Node\Name $name)
	{
		if (!$this->broker->hasConstant($name)) {
			return sprintf('Used constant %s not found.', (string) $name);
		}

		return null;
	}

	/**
	 * @param \PhpParser\Node\Name $name
	 * @return string|null
	 */
	private function checkFunction(Node\Name $name)
	{
		if (!$this->broker->hasFunction($name)) {
			return sprintf('Used function %s not found.', (string) $name);
		}

		$functionReflection = $this->broker->getFunction($name);
		$realName = $functionReflection->getName();
		$usedName = (string) $name;
		if ($realName !== $usedName) {
			return sprintf(
				'Function %s used with incorrect case: %s.',
				$realName,
				$usedName
			);
		}

		return null;
	}

	/**
	 * @param \PhpParser\Node\Name $name
	 * @return string|null
	 */
	private function checkClass(Node\Name $name)
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
