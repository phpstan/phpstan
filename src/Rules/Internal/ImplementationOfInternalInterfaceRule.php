<?php declare(strict_types = 1);

namespace PHPStan\Rules\Internal;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;

class ImplementationOfInternalInterfaceRule implements \PHPStan\Rules\Rule
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
		$errors = [];

		$className = isset($node->namespacedName)
			? (string) $node->namespacedName
			: (string) $node->name;

		try {
			$class = $this->broker->getClass($className);
		} catch (\PHPStan\Broker\ClassNotFoundException $e) {
			return [];
		}

		if ($class->isInternal()) {
			return [];
		}

		foreach ($node->implements as $implement) {
			$interfaceName = (string) $implement;

			try {
				$interface = $this->broker->getClass($interfaceName);

				if (!$interface->isInternal()) {
					continue;
				}

				$interfaceFile = $interface->getFileName();
				if ($interfaceFile === false) {
					continue;
				}

				if ($this->internalScopeHelper->isFileInInternalPaths($interfaceFile)) {
					continue;
				}

				if (!$class->isAnonymous()) {
					$errors[] = sprintf(
						'Class %s implements internal interface %s.',
						$className,
						$interfaceName
					);
				} else {
					$errors[] = sprintf(
						'Anonymous class implements internal interface %s.',
						$interfaceName
					);
				}
			} catch (\PHPStan\Broker\ClassNotFoundException $e) {
				// Other rules will notify if the interface is not found
			}
		}

		return $errors;
	}

}
