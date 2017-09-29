<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PHPStan\Broker\Broker;

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
			$realClassName = $this->broker->getClass($className)->getName();
			if (strtolower($realClassName) !== strtolower($className)) {
				continue; // skip class alias
			}
			if ($realClassName === $className) {
				continue;
			}

			$messages[] = sprintf(
				'Class %s referenced with incorrect case: %s.',
				$realClassName,
				$className
			);
		}

		return $messages;
	}

}
