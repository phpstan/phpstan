<?php declare(strict_types = 1);

namespace PHPStan\Rules\Internal;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ErrorType;

class InstantiationOfInternalClassRule implements \PHPStan\Rules\Rule
{

	/** @var Broker */
	private $broker;

	/** @var RuleLevelHelper */
	private $ruleLevelHelper;

	/** @var InternalScopeHelper */
	private $internalScopeHelper;

	public function __construct(
		Broker $broker,
		RuleLevelHelper $ruleLevelHelper,
		InternalScopeHelper $internalScopeHelper
	)
	{
		$this->broker = $broker;
		$this->ruleLevelHelper = $ruleLevelHelper;
		$this->internalScopeHelper = $internalScopeHelper;
	}

	public function getNodeType(): string
	{
		return New_::class;
	}

	/**
	 * @param New_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[] errors
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$referencedClasses = [];

		if ($node->class instanceof Name) {
			$referencedClasses[] = $scope->resolveName($node->class);
		} elseif ($node->class instanceof Class_) {
			if (!isset($node->class->namespacedName)) {
				return [];
			}

			$referencedClasses[] = $scope->resolveName($node->class->namespacedName);
		} else {
			$classTypeResult = $this->ruleLevelHelper->findTypeToCheck(
				$scope,
				$node->class,
				'', // We don't care about the error message
				function () {
					return true;
				}
			);

			if ($classTypeResult->getType() instanceof ErrorType) {
				return [];
			}

			$referencedClasses = $classTypeResult->getReferencedClasses();
		}

		$errors = [];

		foreach ($referencedClasses as $referencedClass) {
			try {
				$class = $this->broker->getClass($referencedClass);
			} catch (\PHPStan\Broker\ClassNotFoundException $e) {
				continue;
			}

			if (!$class->isInternal()) {
				continue;
			}

			$classFile = $class->getFileName();
			if ($classFile === false) {
				continue;
			}

			if ($this->internalScopeHelper->isFileInInternalPaths($classFile)) {
				continue;
			}

			$errors[] = sprintf(
				'Instantiation of internal class %s.',
				$referencedClass
			);
		}

		return $errors;
	}

}
