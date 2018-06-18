<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;

class UniversalRule implements Rule
{

	/** @var string */
	private $nodeType;

	/** @var callable */
	private $processNodeCallback;

	public function __construct(string $nodeType, callable $processNodeCallback)
	{
		$this->nodeType = $nodeType;
		$this->processNodeCallback = $processNodeCallback;
	}

	public function getNodeType(): string
	{
		return $this->nodeType;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		$callback = $this->processNodeCallback;
		$callback($node, $scope);
	}

}
