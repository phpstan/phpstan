<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;

/**
 * @template TNodeType of \PhpParser\Node
 * @implements Rule<TNodeType>
 */
class UniversalRule implements Rule
{

	/** @var class-string<TNodeType> */
	private $nodeType;

	/** @var (callable(TNodeType, Scope): array<string|RuleError>) */
	private $processNodeCallback;

	/**
	 * @param class-string<TNodeType> $nodeType
	 * @param (callable(TNodeType, Scope): array<string|RuleError>) $processNodeCallback
	 */
	public function __construct(string $nodeType, callable $processNodeCallback)
	{
		$this->nodeType = $nodeType;
		$this->processNodeCallback = $processNodeCallback;
	}

	public function getNodeType(): string
	{
		return $this->nodeType;
	}

	/**
	 * @param TNodeType $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return array<string|RuleError>
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$callback = $this->processNodeCallback;
		return $callback($node, $scope);
	}

}
