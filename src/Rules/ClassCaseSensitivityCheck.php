<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PHPStan\Broker\Broker;
use PHPStan\Rules\RuleErrorBuilder;

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
	 * @return array<mixed>|\PHPStan\Rules\RuleError[]
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

			$messages[] = RuleErrorBuilder::message(sprintf(
				'Class %s referenced with incorrect case: %s.',
				$realClassName,
				$className
			))->identifier('class.caseSensitivity')->build();
		}

		return $messages;
	}

}
