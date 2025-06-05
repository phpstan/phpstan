<?php declare(strict_types = 1);

namespace PHPStan\Rules\Exceptions;

use PhpParser\Node;
use PhpParser\Node\Stmt\Catch_;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\RuleErrorBuilder;

class CaughtExceptionExistenceRule implements \PHPStan\Rules\Rule
{

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	public function __construct(Broker $broker)
	{
		$this->broker = $broker;
	}

	public function getNodeType(): string
	{
		return Catch_::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\Catch_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (isset($node->types)) {
			$classes = $node->types;
		} elseif (isset($node->type)) {
			$classes = [$node->type];
		} else {
			throw new \PHPStan\ShouldNotHappenException();
		}
		$errors = [];
		foreach ($classes as $className) {
			$class = (string) $className;
			if (!$this->broker->hasClass($class)) {
				$errors[] = RuleErrorBuilder::message(sprintf('Caught class %s not found.', $class))
					->identifier('exception.notFound')
					->build();
				continue;
			}

			$classReflection = $this->broker->getClass($class);
			if (!$classReflection->isInterface() && !$classReflection->getNativeReflection()->implementsInterface(\Throwable::class)) {
				$errors[] = RuleErrorBuilder::message(sprintf('Caught class %s is not an exception.', $classReflection->getDisplayName()))
					->identifier('exception.notThrowable')
					->build();
			}
		}

		return $errors;
	}

}
