<?php declare(strict_types = 1);

namespace PHPStan\Rules\Exceptions;

use PhpParser\Node;
use PhpParser\Node\Stmt\Catch_;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\ClassCaseSensitivityCheck;
use PHPStan\Rules\ClassNameNodePair;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;

class CaughtExceptionExistenceRule implements \PHPStan\Rules\Rule
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
		return Catch_::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\Catch_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return RuleError[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$errors = [];
		foreach ($node->types as $class) {
			$className = (string) $class;
			if (!$this->broker->hasClass($className)) {
				$errors[] = RuleErrorBuilder::message(sprintf('Caught class %s not found.', $className))->line($class->getLine())->build();
				continue;
			}

			$classReflection = $this->broker->getClass($className);
			if (!$classReflection->isInterface() && !$classReflection->getNativeReflection()->implementsInterface(\Throwable::class)) {
				$errors[] = RuleErrorBuilder::message(sprintf('Caught class %s is not an exception.', $classReflection->getDisplayName()))->line($class->getLine())->build();
			}

			if (!$this->checkClassCaseSensitivity) {
				continue;
			}

			$errors = array_merge(
				$errors,
				$this->classCaseSensitivityCheck->checkClassNames([new ClassNameNodePair($className, $class)])
			);
		}

		return $errors;
	}

}
