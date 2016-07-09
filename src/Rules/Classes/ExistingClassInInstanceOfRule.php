<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PhpParser\Node\Expr\Instanceof_;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;

class ExistingClassInInstanceOfRule implements \PHPStan\Rules\Rule
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
		return Instanceof_::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\Instanceof_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$class = $node->class;
		if (!($class instanceof \PhpParser\Node\Name)) {
			return [];
		}

		$name = (string) $class;

		if ($name === 'self' || $name === 'static') {
			if ($scope->getClass() === null && !$scope->isInAnonymousClass()) {
				return [
					sprintf('Using %s outside of class scope.', $name),
				];
			}

			if ($name === 'static') {
				return [];
			}

			if ($name === 'self') {
				$name = $scope->getClass();
			}
		}

		if (!$this->broker->hasClass($name)) {
			return [
				sprintf('Class %s not found.', $name),
			];
		}

		return [];
	}

}
