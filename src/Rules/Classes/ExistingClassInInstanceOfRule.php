<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node\Expr\Instanceof_;
use PHPStan\Analyser\Node;
use PHPStan\Broker\Broker;

class ExistingClassInInstanceOfRule implements \PHPStan\Rules\Rule
{

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	/**
	 * @param \PHPStan\Broker\Broker $broker
	 */
	public function __construct(Broker $broker)
	{
		$this->broker = $broker;
	}

	public function getNodeType(): string
	{
		return Instanceof_::class;
	}

	/**
	 * @param \PHPStan\Analyser\Node $node
	 * @return string[]
	 */
	public function processNode(Node $node): array
	{
		$class = $node->getParserNode()->class;
		if (!($class instanceof \PhpParser\Node\Name)) {
			return [];
		}
		$name = (string) $class;

		if ($name === 'self' || $name === 'static') {
			if ($node->getScope()->getClass() === null) {
				return [
					sprintf('Using %s outside of class scope.', $name),
				];
			} else {
				return [];
			}
		}

		if (!$this->broker->hasClass($name)) {
			return [
				sprintf('Class %s does not exist.', $name),
			];
		}

		return [];
	}

}
