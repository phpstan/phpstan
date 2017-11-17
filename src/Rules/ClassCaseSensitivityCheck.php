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
	 * @param string[] $classNames
	 * @return string[]
	 */
	public function checkClassNames(array $classNames): array
	{
		$messages = [];
		foreach ($classNames as $className) {
			if (!$this->broker->hasClass($className)) {
				continue;
			}
			$classReflection = $this->broker->getClass($className);
			$realClassName = $classReflection->getName();
			if (strtolower($realClassName) !== strtolower($className)) {
				continue; // skip class alias
			}
			if ($realClassName === $className) {
				continue;
			}

			$messages[] = sprintf(
				'%s %s referenced with incorrect case: %s.',
				$this->getTypeName($classReflection),
				$realClassName,
				$className
			);
		}

		return $messages;
	}

	private function getTypeName(ClassReflection $classReflection): string
	{
		if ($classReflection->isInterface()) {
			return 'Interface';
		} elseif ($classReflection->isTrait()) {
			return 'Trait';
		}

		return 'Class';
	}

}
