<?php declare(strict_types = 1);

namespace PHPStan\Rules\Internal;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;

class InheritanceOfInternalClassRule implements \PHPStan\Rules\Rule
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
		return Class_::class;
	}

	/**
	 * @param Class_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[] errors
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if ($node->extends === null) {
			return [];
		}

		$errors = [];

		$className = isset($node->namespacedName)
			? (string) $node->namespacedName
			: (string) $node->name;

		try {
			$class = $this->broker->getClass($className);
		} catch (\PHPStan\Broker\ClassNotFoundException $e) {
			return [];
		}

		$parentClassName = (string) $node->extends;

		try {
			$parentClass = $this->broker->getClass($parentClassName);

			if (!$parentClass->isInternal()) {
				return [];
			}

			$parentClassFile = $parentClass->getFileName();
			if ($parentClassFile === false) {
				return [];
			}

			if ($this->internalScopeHelper->isFileInInternalPaths($parentClassFile)) {
				return [];
			}

			if (!$class->isAnonymous()) {
				$errors[] = sprintf(
					'Class %s extends internal class %s.',
					$className,
					$parentClassName
				);
			} else {
				$errors[] = sprintf(
					'Anonymous class extends internal class %s.',
					$parentClassName
				);
			}
		} catch (\PHPStan\Broker\ClassNotFoundException $e) {
			// Other rules will notify if the interface is not found
		}

		return $errors;
	}

}
