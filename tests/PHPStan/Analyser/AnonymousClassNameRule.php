<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Broker\Broker;
use PHPStan\Rules\Rule;

class AnonymousClassNameRule implements Rule
{

	/** @var Broker */
	private $broker;

	public function __construct(Broker $broker)
	{
		$this->broker = $broker;
	}

	public function getNodeType(): string
	{
		return Class_::class;
	}

	/**
	 * @param Class_ $node
	 * @param Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$className = isset($node->namespacedName)
			? (string) $node->namespacedName
			: (string) $node->name;
		try {
			$this->broker->getClass($className);
		} catch (\PHPStan\Broker\ClassNotFoundException $e) {
			return ['not found'];
		}

		return ['found'];
	}

}
