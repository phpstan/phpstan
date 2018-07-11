<?php declare(strict_types = 1);

namespace PHPStan\Rules\Internal;

use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;

class InheritanceOfInternalInterfaceRule implements \PHPStan\Rules\Rule
{

	/** @var Broker */
	private $broker;

	/** @var InternalScopeHelper */
	private $internalScopeHelper;

	public function __construct(Broker $broker, InternalScopeHelper $internalScopeHelper)
	{
		$this->broker = $broker;
		$this->internalScopeHelper = $internalScopeHelper;
	}

	public function getNodeType(): string
	{
		return Interface_::class;
	}

	/**
	 * @param Interface_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[] errors
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if ($node->extends === null) {
			return [];
		}

		$interfaceName = isset($node->namespacedName)
			? (string) $node->namespacedName
			: (string) $node->name;

		try {
			$interface = $this->broker->getClass($interfaceName);
		} catch (\PHPStan\Broker\ClassNotFoundException $e) {
			return [];
		}

		if ($interface->isInternal()) {
			return [];
		}

		$errors = [];

		foreach ($node->extends as $parentInterfaceName) {
			$parentInterfaceName = (string) $parentInterfaceName;

			try {
				$parentInterface = $this->broker->getClass($parentInterfaceName);

				if (!$parentInterface->isInternal()) {
					continue;
				}

				$parentInterfaceFile = $parentInterface->getFileName();
				if ($parentInterfaceFile === false) {
					continue;
				}

				if ($this->internalScopeHelper->isFileInInternalPaths($parentInterfaceFile)) {
					continue;
				}

				$errors[] = sprintf(
					'Interface %s extends internal interface %s.',
					$interfaceName,
					$parentInterfaceName
				);
			} catch (\PHPStan\Broker\ClassNotFoundException $e) {
				// Other rules will notify if the interface is not found
			}
		}

		return $errors;
	}

}
