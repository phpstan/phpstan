<?php declare(strict_types = 1);

namespace App;

use PhpParser\ErrorHandler\Collecting;
use PhpParser\Node;

class CodeSanitizer
{

	/** @var \PhpParser\Parser */
	private $parser;

	/** @var \PhpParser\PrettyPrinter\Standard */
	private $printer;

	/** @var Collecting */
	private $errorHandler;

	public function __construct(
		\PhpParser\Parser $parser,
		\PhpParser\PrettyPrinter\Standard $printer,
		Collecting $errorHandler
	)
	{
		$this->parser = $parser;
		$this->printer = $printer;
		$this->errorHandler = $errorHandler;
	}


	public function sanitize(string $code): string
	{
		$allNodes = $this->parser->parse($code, $this->errorHandler) ?? [];
		if ($this->errorHandler->hasErrors()) {
			return '';
		}
		$filteredNodes = $this->filterNodes($allNodes);
		return $this->printer->prettyPrintFile($filteredNodes);
	}


	/**
	 * @param  Node[] $nodes
	 * @return Node[]
	 */
	private function filterNodes(array $nodes, string $namespace = null): array
	{
		$nodes = array_filter($nodes, static function (Node $node) use ($namespace) {
			return $node instanceof Node\Stmt\Namespace_
				|| $node instanceof Node\Stmt\Function_
				|| $node instanceof Node\Stmt\Use_
				|| $node instanceof Node\Stmt\Class_
				|| $node instanceof Node\Stmt\Interface_
				|| $node instanceof Node\Stmt\Trait_;
		});

		foreach ($nodes as $i => $node) {
			if ($node instanceof Node\Stmt\Namespace_) {
				$node->stmts = $this->filterNodes($node->stmts, $node->name !== null ? $node->name->toString() : null);
				continue;
			}
			if ($node instanceof Node\Stmt\Use_ && $namespace === null) {
				$node->uses = array_values(array_filter($node->uses, function (Node\Stmt\UseUse $useUse): bool {
					return count($useUse->name->parts) > 1;
				}));
				if (count($node->uses) === 0) {
					unset($nodes[$i]);
				}
			}
		}

		$nodes = array_values($nodes); // required for PhpParser\Parser

		return $nodes;
	}

}
