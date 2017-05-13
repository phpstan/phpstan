<?php declare(strict_types = 1);

namespace PHPStan\Rules\Constants;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;

class ConstantRule implements \PHPStan\Rules\Rule
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
		return Node\Expr\ConstFetch::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\ConstFetch $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$this->broker->hasConstant($node->name, $scope)) {
			return [
				sprintf(
					'Constant %s not found.',
					(string) $node->name
				),
			];
		}

		return [];
	}

}
