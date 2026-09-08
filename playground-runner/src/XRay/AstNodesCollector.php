<?php declare(strict_types = 1);

namespace PHPStanPlayground\XRay;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\FileNode;
use function array_map;
use function array_pop;
use function array_values;
use function count;
use function get_debug_type;
use function get_object_vars;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function json_encode;
use function mb_strlen;
use function mb_substr;
use function spl_object_id;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * Walks the whole parsed AST once and records every node: its kind, its byte
 * range in the file, the index of its parent and its sub-nodes. Nodes come out
 * in pre-order, so a parent always precedes its children.
 *
 * A sub-node value is the index of the child node, a list of those for arrays,
 * or a rendered scalar ("'friday'", 0, true, null).
 *
 * @implements Collector<FileNode, list<array{string, int, int, int, list<array{string, int|string|list<int|string>}>}>>
 */
final class AstNodesCollector implements Collector
{

	public function getNodeType(): string
	{
		return FileNode::class;
	}

	/**
	 * @return list<array{string, int, int, int, list<array{string, int|string|list<int|string>}>}>
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$visitor = new class extends NodeVisitorAbstract {

			/** @var list<array{string, int, int, int, list<array{string, int|string|list<int|string>}>}> */
			public array $nodes = [];

			/** @var list<int> */
			private array $stack = [];

			/** @var array<int, int> */
			private array $indexByObjectId = [];

			public function enterNode(Node $node): ?int
			{
				if (!$node->hasAttribute('startFilePos') || !$node->hasAttribute('endFilePos')) {
					return NodeTraverser::DONT_TRAVERSE_CHILDREN;
				}

				$parent = count($this->stack) > 0 ? $this->stack[count($this->stack) - 1] : -1;
				$this->nodes[] = [
					NodeKind::of($node),
					$node->getStartFilePos(),
					$node->getEndFilePos() + 1,
					$parent,
					[],
				];
				$index = count($this->nodes) - 1;
				$this->indexByObjectId[spl_object_id($node)] = $index;
				$this->stack[] = $index;

				return null;
			}

			public function leaveNode(Node $node): null
			{
				if (!$node->hasAttribute('startFilePos') || !$node->hasAttribute('endFilePos')) {
					return null;
				}

				// Children have been indexed by now, so sub-nodes can point at them.
				$index = array_pop($this->stack);
				if ($index === null) {
					return null;
				}
				$vars = get_object_vars($node);
				$props = [];
				foreach ($node->getSubNodeNames() as $name) {
					$props[] = [$name, $this->describe($vars[$name] ?? null)];
				}
				$this->nodes[$index][4] = $props;

				return null;
			}

			/**
			 * @return int|string|list<int|string>
			 */
			private function describe(mixed $value): int|string|array
			{
				if ($value instanceof Node) {
					return $this->indexByObjectId[spl_object_id($value)] ?? NodeKind::of($value);
				}
				if (is_array($value)) {
					return array_map(
						fn (mixed $item): int|string => $item instanceof Node
							? ($this->indexByObjectId[spl_object_id($item)] ?? NodeKind::of($item))
							: $this->scalar($item),
						array_values($value),
					);
				}

				return $this->scalar($value);
			}

			private function scalar(mixed $value): string
			{
				if (is_string($value)) {
					if (mb_strlen($value) > 60) {
						$value = mb_substr($value, 0, 60) . '…';
					}
					$json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

					return $json === false ? '"…"' : $json;
				}
				if (is_bool($value)) {
					return $value ? 'true' : 'false';
				}
				if ($value === null) {
					return 'null';
				}
				if (is_int($value) || is_float($value)) {
					return (string) $value;
				}

				return get_debug_type($value);
			}

		};

		$traverser = new NodeTraverser();
		$traverser->addVisitor($visitor);
		$traverser->traverse($node->getNodes());

		return $visitor->nodes;
	}

}
