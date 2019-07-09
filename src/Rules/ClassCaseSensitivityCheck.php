<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassReflection;

class ClassCaseSensitivityCheck
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	public function __construct(Broker $broker)
	{
		$this->broker = $broker;
	}

	/**
	 * @param ClassNameNodePair[] $pairs
	 * @return RuleError[]
	 */
	public function checkClassNames(array $pairs): array
	{
		$errors = [];
		foreach ($pairs as $pair) {
			$className = $pair->getClassName();
			if (!$this->broker->hasClass($className)) {
				continue;
			}
			$classReflection = $this->broker->getClass($className);
			if ($classReflection->getFileName() === false) {
				continue; // skip built-in classes
			}
			$realClassName = $classReflection->getName();
			if (strtolower($realClassName) !== strtolower($className)) {
				continue; // skip class alias
			}
			if ($realClassName === $className) {
				continue;
			}

			$errors[] = RuleErrorBuilder::message(sprintf(
				'%s %s referenced with incorrect case: %s.',
				$this->getTypeName($classReflection),
				$realClassName,
				$className
			))->line($pair->getNode()->getLine())->build();
		}

		return $errors;
	}

	private function getTypeName(ClassReflection $classReflection): string
	{
		if ($classReflection->isInterface()) {
			return 'Interface';
		}
		if ($classReflection->isTrait()) {
			return 'Trait';
		}

		return 'Class';
	}

}
